<?php

namespace App\CloudProviders;

use App\Booty;
use App\Snapshot;
use GrahamCampbell\DigitalOcean\Facades\DigitalOcean;

class DigitalOceanService
{
    private $defaultSize;
    private $defaultRegion;

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
        DigitalOcean::droplet()->snapshot($booty_id, $snapshotName);
        $snapshot->status = 'Building new snapshot';
        $snapshot->name = $snapshotName;
        $snapshot->save();
    }

    /**
     * Deletes the specified virtual machine
     *
     * @param  string $internal_machine_id
     * @return void
     */
    public function deleteVM(String $internal_machine_id)
    {
        DigitalOcean::droplet()->delete($internal_machine_id);
    }

    /**
     * Deletes the specified image snapshot
     *
     * @param  string $internal_snapshot_id
     * @return void
     */
    public function deleteSnapshot(String $internal_snapshot_id)
    {
        DigitalOcean::image()->delete($internal_snapshot_id);
    }

    // public function deleteSnapshot(Snapshot $image)
    // {
    //     $snapshots = DigitalOcean::image()->getAll(['private' => true]);
    //     $status = $image->status;
    //     foreach($snapshots as $snapshot) {
    //         if ($snapshot->name === $image->name) {
    //             DigitalOcean::image()->delete($snapshot->id);
    //             $status = 'Snapshot Deletion Requested';
    //             break;
    //         }
    //     }
    //     $image->status = $status;
    //     $image->save();

    // }

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
        } catch (\Exception $e) {
            $status = 'Booty missing';
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
        $privateImages = DigitalOcean::image()->getAll(['private' => true]);

        foreach ($privateImages as $image) {
            if ($image->name === $snapshot->name) {
                $snapshot->status = 'Snapshot Ready';
                $snapshot->internal_snapshot_id = $image->id;

                return $snapshot->save();
            }
        }

        $snapshot->status = 'Snapshot Not Found';
        $snapshot->internal_snapshot_id = null;

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
        $cloudInitCommand = $this->_cloudInitFor(
            $booty->app,
            json_decode($booty->services, true)
        );

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
    }

    public function finaliseBooty(Booty $booty)
    {
        $status = 'Live';
        try {
            $droplet = DigitalOcean::droplet()->getById($booty->internal_machine_id);
            $booty->ip = $droplet->networks[0]->ipAddress;
            $booty->ssl_renewed_at = now();
        } catch (\Exception $e) {
            $status = 'Provisioning Error';
        }

        $booty->status = $status;
        $booty->save();
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
    private function _cloudInitFor($appName, $services)
    {
        // DEFAULT services
        $commands = '#!/bin/bash'
            . "\n"
            . 'php ' . '/var/www/app/' . $appName . '/' . 'artisan key:generate '
            . "\n";

        // QUEUE service
        if (array_key_exists('laravel-queue', $services) && $services['laravel-queue']) {
            $commands .= 'php ' . '/var/www/app/' . $appName . '/' . 'artisan queue:restart '
            . "\n"
            . 'supervisorctl start laravel-worker:*'
            . "\n";
        }

        // PASSPORT service
        if (array_key_exists('laravel-passport', $services) && $services['laravel-passport']) {
            $commands .= 'php ' . '/var/www/app/' . $appName . '/' . 'artisan passport:install '
                . "\n";
        }

        // COMMANDS executions
        if (array_key_exists('commands', $services)) {
            foreach ($services['commands'] as $command) {
                $commands .= "sudo -H -u appuser bash -c '" . $command . "'; " . "\n";
            }
        }

        return $commands;
    }
}
