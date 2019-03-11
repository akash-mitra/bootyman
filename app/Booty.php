<?php

namespace App;

use App\Jobs\orderBootyProvision;
use App\Jobs\confirmBootyProvision;
use Illuminate\Database\Eloquent\Model;
use App\CloudProviders\DigitalOceanService;

class Booty extends Model
{
    protected $fillable = [
        'snapshot_id',
        'order_id',
        'owner_email',
        'name',
        'ip',
        'status',
        'internal_machine_id',
        'size',
        'region',
        'backup',
        'monitoring',
        'sshkey',
        'ssl_renewed_at'
    ];

    public static function provision ($orderId, $ownerEmail, $name, $size, $region, $provider)
    {
        $cloudProvider = self::getCloudProvider($provider);
        $snapshot = Snapshot::latestReady();

        $booty = new Booty ([
            'snapshot_id' => $snapshot->id,
            'order_id' => $orderId,
            'owner_email' => $ownerEmail,
            'name' => $name,
            'ip' => null,
            'status' => 'Initiated',
            'size' => $size,
            'region' => $region,
            'backup' => true,
            'monitoring' => false,
            'sshkey' => null,
            'ssl_renewed_at' => null
        ]);

        $booty->save();
        orderBootyProvision::dispatch($cloudProvider, $booty)->onConnection( 'booty-assembly-line' );
        confirmBootyProvision::dispatch($cloudProvider, $booty)->onConnection( 'booty-assembly-line' )
            ->delay(now()->addMinutes(5));

        return $booty;
    }



    /**
     * Returns a service class corresponding to the cloud service selected
     *
     * @param [type] $provider
     * @return void
     */
    private static function getCloudProvider($provider)
    {
        $cloudProvider = null;

        if (strtoupper($provider) ==='DO')  {
            $cloudProvider = new DigitalOceanService();
        }

        return $cloudProvider;
    }
}
