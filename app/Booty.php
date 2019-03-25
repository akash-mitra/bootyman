<?php

namespace App;

use Illuminate\Http\Request;
use App\Jobs\orderVMCreate;
use App\Jobs\orderVMDelete;
use App\Jobs\confirmVMStatus;
use App\Jobs\orderBootyProvision;
use App\Jobs\confirmBootyProvision;
use App\Jobs\orderDomainNameChange;
use App\Jobs\confirmDomainNameChange;
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
        'provider',
        'region',
        'size',
        'type',
        'backup',
        'monitoring',
        'sshkey',
        'ssl_renewed_at',
        'app',
        'source_code',
        'branch',
        'commit',
        'env',
        'services'
    ];


    /**
     * Returns the original snapshot from where this booty was provisioned.
     * If this booty was created fresh and not provisioned from any
     * snapshot, this will return null.
     *
     * @return void
     */
    public function origin()
    {
        return $this->belongsTo(Snapshot::class, 'snapshot_id', 'id');
    }


    /**
     * Deletes the underline VM permanently for the booty
     *
     * @return void
     */
    public function terminate()
    {
        $cloudProvider = self::getCloudProvider($this->provider);

        orderVMDelete::dispatch($cloudProvider, $this->internal_machine_id)->onConnection('booty-assembly-line');

        $this->status = 'Deleted';
        $this->save();

        return $this;
    }




    /**
     * Orders the cloud service provider to build a new image 
     * and then configures the application using code from source control.
     *
     * @param Request $request
     * @return App\Booty
     */
    public static function order(Request $request)
    {
        $provider = empty($request->input('provider')) ? 'DO' : $request->input('provider');

        $cloudProvider = self::getCloudProvider($provider);

        $booty = new Booty([
            'order_id' => empty($request->input('order_id')) ? 0 : $request->input('order_id'),
            'owner_email' =>  empty($request->input('orderer')) ? auth()->user()->email : $request->input('orderer'),
            'status' => 'Initiated',
            'provider' =>  $provider,
            'size' => empty($request->input('size')) ? config('services.infra.size') : $request->input('size'),
            'region' => empty($request->input('region')) ? config('services.infra.region') : $request->input('region'),
            'type' => empty($request->input('type')) ? config('services.infra.os') : $request->input('type'),
            'backup' => false,
            'monitoring' => false,
            'sshkey' => empty($request->input('sshkey')) ? config('services.infra.sshkey') : $request->input('sshkey'),
            'app' => $request->input('app'),
            'source_code' => $request->input('source_code'),
            'branch' => empty($request->input('branch')) ? 'master' : $request->input('branch'),
            'commit' => empty($request->input('commit_id')) ? 'latest' : $request->input('commit_id'),
            'env' => env('APP_ENV')
        ]);

        $booty->save();

        orderVMCreate::dispatch($cloudProvider, $booty)->onConnection('booty-assembly-line');
        confirmVMStatus::dispatch($cloudProvider, $booty)->onConnection('booty-assembly-line')
            ->delay(now()->addMinutes(15));

        return $booty;
    }



    
    // public static function provision ($orderId, $ownerEmail, $name, $size, $region, $provider)
    // {
    //     $cloudProvider = self::getCloudProvider($provider);
    //     $snapshot = Snapshot::latestReady();

    //     $booty = new Booty ([
    //         'snapshot_id' => $snapshot->id,
    //         'order_id' => $orderId,
    //         'owner_email' => $ownerEmail,
    //         'name' => $name,
    //         'ip' => null,
    //         'status' => 'Initiated',
    //         'size' => $size,
    //         'region' => $region,
    //         'backup' => true,
    //         'monitoring' => false,
    //         'sshkey' => null,
    //         'ssl_renewed_at' => null
    //     ]);

    //     $booty->save();
    //     orderBootyProvision::dispatch($cloudProvider, $booty)->onConnection( 'booty-assembly-line' );
    //     confirmBootyProvision::dispatch($cloudProvider, $booty)->onConnection( 'booty-assembly-line' )
    //         ->delay(now()->addMinutes(5));

    //     return $booty;
    // }


    /**
     * Sets the given domain name to the booty
     *
     * @param Booty $booty
     * @param String $domainName
     * @param String $provider
     * @return void
     */
    public static function setDomainName (Booty $booty, String $domainName, String $provider)
    {
        $cloudProvider = self::getCloudProvider($provider);

        orderDomainNameChange::dispatch($cloudProvider, $booty, $domainName)->onConnection('booty-assembly-line');

        confirmDomainNameChange::dispatch($cloudProvider, $booty, $domainName)->onConnection('booty-assembly-line')
            ->delay(now()->addMinutes(2));

        $booty->name = 'Updating';

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
