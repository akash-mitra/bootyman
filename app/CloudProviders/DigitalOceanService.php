<?php

namespace App\CloudProviders;

use App\Booty;
use App\Snapshot;
use App\Journal;
use Exception;
use GrahamCampbell\DigitalOcean\Facades\DigitalOcean;
use Illuminate\Support\Str;

class DigitalOceanService
{
    private $defaultSize;
    private $defaultRegion;
    private $order_id;
    private $commands = [];

    public function __construct()
    {
        $this->defaultSize = 's-1vcpu-1gb';
        $this->defaultRegion = 'sgp1';
    }

    /**
     * This creates a fresh new virtual machine and installs the application
     * code in that new machine.
     *
     * @param  Booty $booty
     * @return void
     */
    public function createVM(Booty $booty)
    {
        $cloudInitCommand = '#!/bin/bash'
            . "\n"
            . 'curl --output /root/b.sh https://raw.githubusercontent.com/akash-mitra/booty/master/booty.sh'
            . "\n"
            . 'bash /root/b.sh ' . $booty->source_code;

        try {
            $droplet = DigitalOcean::droplet()->create(
                empty($booty->name) ? 'domain-unassigned' : $booty->name, // name
                $booty->region, // region
                $booty->size,         // size - minimum size
                $booty->type,         // image - public image slug
                $booty->backup,       // backup
                false,                // ipv6
                false,                // private networking
                [$booty->sshkey],     // ssh keys
                $cloudInitCommand,    // user data
                $booty->monitoring    // monitoring
            );

            $booty['internal_machine_id'] = $droplet->id;
            $booty->status = 'Building new booty';
            $booty->save();

            Journal::info('CloudService: create VM request processed', 0, __METHOD__, $this->order_id);
        } catch (Exception $e) {

            Journal::error(get_class($e) . ': ' . $e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['booty' => $booty->toArray()]);
            throw $e;
        }
    }

    /**
     * Orders DO API for creating a new Snaspshot from the
     * provided image id.
     *
     * @param  App\Snapshot $image
     * @return void
     */
    public function createSnapshot(Snapshot $snapshot)
    {
        $booty_id = $snapshot->origin->internal_machine_id;
        $snapshotName = $snapshot->name . $snapshot->id;

        try {
            DigitalOcean::droplet()->snapshot($booty_id, $snapshotName);
            $snapshot->status = 'Building new snapshot';
            $snapshot->name = $snapshotName;
            $snapshot->save();

            Journal::info('CloudService: create Snapshot request processed.', 0, __METHOD__, $this->order_id);
        } catch (Exception $e) {

            Journal::error(get_class($e) . ': ' . $e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['snapshot' => $snapshot->toArray()]);
            throw $e;
        }
    }

    /**
     * Deletes the specified virtual machine
     *
     * @param  string $internal_machine_id
     * @return void
     */
    public function deleteVM(String $internal_machine_id)
    {
        try {
            DigitalOcean::droplet()->delete($internal_machine_id);
            Journal::info('CloudService: delete VM request processed.', 0, __METHOD__, $this->order_id);
        } catch (Exception $e) {

            Journal::error(get_class($e) . ': ' . $e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['internal_machine_id' => $internal_machine_id]);
            throw $e;
        }
    }

    /**
     * Deletes the specified image snapshot
     *
     * @param  string $internal_snapshot_id
     * @return void
     */
    public function deleteSnapshot(String $internal_snapshot_id)
    {
        try {
            DigitalOcean::image()->delete($internal_snapshot_id);
            Journal::info('CloudService: delete Snapshot request processed.', 0, __METHOD__, $this->order_id);
        } catch (Exception $e) {
            Journal::error(get_class($e) . ': ' . $e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['internal_snapshot_id' => $internal_snapshot_id]);
            throw $e;
        }
    }

    

    /**
     * Given a booty, it checks if the booty actually exists in
     * the digitalocean's repository and reaffirms/writes back
     * the status of the machine.
     *
     * @param  Booty $booty
     * @return void
     */
    public function confirmVM(Booty $booty)
    {
        $status = 'Live';
        try {
            $droplet = DigitalOcean::droplet()->getById($booty->internal_machine_id);
            $booty->ip = $droplet->networks[0]->ipAddress;
            Journal::info('CloudService: Confirmed VM exists.', 0, __METHOD__, $this->order_id);
        } catch (Exception $e) {
            $status = 'Booty missing';
            Journal::warning($e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['booty' => $booty->toArray()]);
        }

        $booty->status = $status;
        $booty->save();
    }


    /**
     * Given a snapshot, this checks in the digitalocean's repo
     * if that snapshot really exists as an image there and
     * writes back the status in the snapshot table.
     *
     * @param  Snapshot $snapshot
     * @return void
     */
    public function confirmSnapshot(Snapshot $snapshot)
    {

        if ($snapshot->status === 'Snapshot Ready') return;
                
        try {
            $privateImages = DigitalOcean::image()->getAll(['private' => true]);
            Journal::info('CloudService: Received list of snapshots.', 0, __METHOD__, $this->order_id);
        } catch (Exception $e) {
            Journal::error(get_class($e) . ': ' . $e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['snapshot' => $snapshot->toArray()]);
            throw $e;
        }

        foreach ($privateImages as $image) {
            if ($image->name === $snapshot->name) {
                $snapshot->status = 'Snapshot Ready';
                $snapshot->internal_snapshot_id = $image->id;

                return $snapshot->save();
            }
        }

        $snapshot->status = 'Snapshot Not Found';
        $snapshot->internal_snapshot_id = null;
        Journal::warning('Snapshot not found in cloud', 0, __METHOD__, $this->order_id, ['snapshot' => $snapshot->toArray()]);
        return $snapshot->save();
    }

    /**
     * Provisions a new booty based on the latest image of the application
     *
     * @param  Booty $booty
     * @return void
     */
    public function provisionVM(Booty $booty)
    {
        $cloudInitCommand = $this->cloudProvisionScript(
            $booty->app,
            json_decode($booty->services, true)
        );

        try {
            $droplet = DigitalOcean::droplet()->create(
                $booty->order_id,     // name
                $booty->region, // region
                $booty->size,         // size - minimum size
                $booty->origin->internal_snapshot_id,  // image - public image slug
                $booty->backup,       // backup
                false,                // ipv6
                false,                // private networking
                [$booty->sshkey],     // ssh keys
                $cloudInitCommand,    // user data
                $booty->monitoring    // monitoring
            );
            $booty['internal_machine_id'] = $droplet->id;
            $booty->save();
            Journal::info('CloudService: provision VM request processed.', 0, __METHOD__, $this->order_id, [
                'services' => json_decode($booty->services, true),
                'init_script' =>  $cloudInitCommand,
                'from' => $booty->origin->internal_snapshot_id,
                'sshkey' => $booty->sshkey
            ]);
        } catch (Exception $e) {
            Journal::error(get_class($e) . ': ' . $e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['booty' => $booty->toArray()]);
            throw $e;
        }
    }

    public function finaliseBooty(Booty $booty)
    {
        if ($booty->status == 'Live') 
            return;

        $status = 'Live';
        try {
            $droplet = DigitalOcean::droplet()->getById($booty->internal_machine_id);
            $booty->ip = $droplet->networks[0]->ipAddress;
            Journal::info('CloudService: VM Provisioned.', 0, __METHOD__, $this->order_id);
        } catch (\Exception $e) {
            $status = 'Provisioning Error';
            Journal::warning($e->getMessage(), $e->getCode(), __METHOD__, $this->order_id, ['booty' => $booty->toArray()]);
        }

        $booty->status = $status;
        $booty->save();

        $password = Str::random(8);

        Journal::info('CloudService: Admin Password Set.', 0, __METHOD__, $this->order_id, [
            'ipaddress' => $booty->ip,
            'email' => $booty->owner_email,
            'password' => $password
        ]);

        $localpath = env('BOOTYMAN_ROOT');
        $port = env('SSH_PORT');
        $key = env('PRIVATE_KEY');
        $remotepath = env('WEBAPP_ROOT');

        shell_exec("cd " . $localpath . " && envoy run deploy --ipaddress=" . $booty->ip . " --port=" . $port . " --key=" . $key . " --remotepath=" . $remotepath . " --email=" . $booty->owner_email . " --password=" . $password);
    }




    public function changeDomainName(Booty $booty, String $domainName)
    {
        try {
            DigitalOcean::domain()->create($domainName, $booty->ip);
            $booty->name = 'Updating...';
            $booty->save();
        } catch (\Exception $exception) {
            \Log::warn('Failed to assign domain [' . $domainName . '] to booty [' . $booty->id . ']');
            \Log::warn($exception->getMessage());
        }
    }

    public function confirmDomainName(Booty $booty, String $domainName)
    {
        try {
            $domainObject = DigitalOcean::domain()->getByName($domainName);
            if (strpos($domainObject->zoneFile, 'IN A ' . $booty->ip) !== false) {
                $booty->name = $domainName;
            } else {
                throw new \Exception('Domian [' . $domainName . '] is not associated to IP of the booty');
            }
        } catch (\Exception $exception) {
            \Log::warn('Failed to assign domain [' . $domainName . '] to booty [' . $booty->id . ']');
            \Log::warn($exception->getMessage());
            $booty->name = 'Error!';
        }

        $booty->save();
    }



    public static function resources()
    {
        return [
            'machines' => DigitalOcean::droplet()->getAll(),
            'images' => DigitalOcean::image()->getAll(['private' => true])
        ];
    }

    /**
     * Creates the cloud init script to be run on  VM provision.
     *
     * @param  string $appName
     * @param  array  $services
     * @return string
     */
    private function cloudProvisionScript($appName, $services)
    {
        $init = new CloudInitBash('/var/www/app/' . $appName);
        $init->artisan('key:generate');

        // add user service
        if ($this->serviceHas($services, 'laravel-add-user')) {
            $param = $services['laravel-add-user'];
            $init->artisan('user:add "' . $param['name'] . '" ' . $param['email'] . ' ' . $param['password'] . ' admin');
        }

        // PASSPORT service
        if ($this->serviceHas($services, 'laravel-passport')) {
            $init->artisan('passport:install');
        }

        // QUEUE service
        if ( $this->serviceHas($services, 'laravel-queue') ) {
            $init->artisan('queue:restart');
            $init->command( 'supervisorctl start laravel-worker:*');
        }

        if ($this->serviceHas($services, 'environment')) {
            $props = $services['environment'];
            foreach($props as $key => $value) {
                $init->env($key, $value);
            }
        }

        // COMMANDS executions
        if ($this->serviceHas($services, 'commands')) {
            foreach ($services['commands'] as $command) {
                $init->command("sudo -H -u appuser bash -c '" . $command . "'");
            }
        }

        return $init->getCloudInitScript();
    }




    /**
     * This can be used to set (or change) an order Id for the
     * cloud service. Some operations may use this order id as
     * correlation id while logging error or information etc.
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }


    private function serviceHas($services, $service_key)
    {
        return array_key_exists($service_key, $services) && ! empty($services[$service_key]);
    }
}
