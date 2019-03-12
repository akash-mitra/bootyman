<?php

namespace App\CloudProviders;

use App\Booty;
use App\Snapshot;
use GrahamCampbell\DigitalOcean\Facades\DigitalOcean;

class DigitalOceanService 
{
    private $defaultSize;
    private $defaultRegion;

    public function __construct() {
        
        $this->defaultSize = 's-1vcpu-1gb';
        $this->defaultRegion = 'sgp1';
    }


    public function createImage (Snapshot $image) 
    {    
        $cloudInitCommand = '#!/bin/bash'
                            . "\n"
                            . 'curl --output /root/b.sh https://raw.githubusercontent.com/akash-mitra/booty/master/booty.sh'
                            . "\n"
                            . 'bash /root/b.sh ' . $image->source_code;
        
        $droplet = DigitalOcean::droplet()->create(
            $image->commit_id,    // name
            $this->defaultRegion, // region 
            $this->defaultSize,   // size - minimum size
            $image->type,         // image - public image slug
            false,                // backup
            false,                // ipv6
            false,                // private networking
            [60344],              // ssh keys
            $cloudInitCommand,    // user data
            false                 // monitoring
        );

        $image['resource_id'] = $droplet->id;
        $image->status = 'Image build requested';
        $image->save();
    }

    /**
     * Orders DO API for creating a new Snaspshot from the 
     * provided image id.  
     *
     * @param App\Snapshot $image
     * @return void
     */
    public function createSnapshot(Snapshot $image)
    {
        $snapshotName = now()->format('Ymd') . 'D' . $image->resource_id . 'N' . $image->id;
        DigitalOcean::droplet()->snapshot($image->resource_id, $snapshotName);
        $image->status = 'Snapshot build requested';
        $image->name = $snapshotName;
        $image->save();
    }


    public function deleteImage(Snapshot $image)
    {
        DigitalOcean::droplet()->delete($image->resource_id);
        $image->resource_id = 0;
        $image->save();
    }


    public function deleteSnapshot(Snapshot $image)
    {
        $snapshots = DigitalOcean::image()->getAll(['private' => true]);
        $status = $image->status;
        foreach($snapshots as $snapshot) {
            if ($snapshot->name === $image->name) {
                DigitalOcean::image()->delete($snapshot->id);
                $status = 'Snapshot Deletion Requested';
                break;
            }
        }
        $image->status = $status;
        $image->save();

    }


    public function checkImageIsPresent(Snapshot $image)
    {
        $status = 'Image Ready';
        try {
            $droplet = DigitalOcean::droplet()->getById($image->resource_id);
        } catch(\Exception $e) {
            $status = 'Image missing';
        }

        $image->status = $status;
        $image->save();
    }



    public function checkSnapshotIsPresent(Snapshot $image)
    {
        $snapshots = DigitalOcean::image()->getAll(['private' => true]);
        foreach($snapshots as $snapshot) {
            if ($snapshot->name === $image->name) 
            {
                $image->status = 'Snapshot Ready';
                $image->internal_snapshot_id = $snapshot->id;
                return $image->save();
            }
        }
    }
    


    public function createBooty (Booty $booty) 
    {
        $internalSnapshotId = Snapshot::latestReady()->internal_snapshot_id;

        $cloudInitCommand = '';

        $droplet = DigitalOcean::droplet()->create(
            $booty->order_id,     // name
            $booty->region,       // region 
            $booty->size,         // size - minimum size
            $internalSnapshotId,  // image - public image slug
            $booty->backup,       // backup
            false,                // ipv6
            false,                // private networking
            [60344],              // ssh keys
            $cloudInitCommand,    // user data
            $booty->monitoring    // monitoring
        );

        $booty[ 'internal_machine_id' ] = $droplet->id;
        $booty->status = 'Provisioning In-progress';
        $booty->save();
    }


    public function finaliseBooty (Booty $booty)
    {
        $status = 'Live';
        try {
            $droplet = DigitalOcean::droplet()->getById($booty->internal_machine_id);
            $booty->ip = $droplet->networks[0]->ipAddress;
            $booty->ssl_renewed_at = now();
        } catch(\Exception $e) {
            $status = 'Provisioning Error';
        }

        $booty->status = $status;
        $booty->save();
    }


    public static function resources ()
    {
        return [
            "machines" => DigitalOcean::droplet()->getAll(),
            "images" => DigitalOcean::image()->getAll(['private' => true])
        ];
    }   

    

}
