<?php

namespace App;

use App\Booty;

use App\Jobs\confirmVMStatus;
use App\Jobs\orderVMProvision;
use App\Jobs\orderSnapshotCreate;
use App\Jobs\orderSnapshotDelete;
use App\Jobs\confirmSnapshotStatus;
use Illuminate\Database\Eloquent\Model;
use App\CloudProviders\DigitalOceanService;

class Snapshot extends Model
{
    protected $fillable = [ 'name', 'app', 'provider', 'booty_id', 'internal_snapshot_id', 'status', 'env', 'order_id', 'owner_email'];


    public function booties()
    {
        return $this->hasMany(Booty::class);
    }


    public function origin()
    {
        return $this->belongsTo(Booty::class, 'booty_id', 'id');
    }

    

    /**
     * Orders the creation of a new snapshot from the booty id provided.
     *
     * @param String $bootyId
     * @param String $provider
     * @return void
     */
    public static function order (String $bootyId, String $provider, String $orderId, String $orderer)
    {
        $booty = Booty::findOrFail($bootyId);

        if ($booty->status != 'Live' && $booty->status != 'Booty down') {
            throw new \Exception('Can not create snapshot if source booty is not in Live or Booty down state');
        }

        $cloudProvider = self::getCloudProvider($provider);

        $snapshot = new Snapshot([
            'name' => now()->format('Ymdhi') . '-',
            'app' => $booty->app,
            'provider' => $provider,
            'booty_id' => $booty->id,
            'internal_snapshot_id' => null,
            'status' => 'Initiated',
            'env' => env('APP_ENV'),
            'order_id' => $orderId,
            'owner_email' => $orderer
        ]);

        $snapshot->save();

        orderSnapshotCreate::dispatch($cloudProvider, $snapshot)->onConnection( 'booty-assembly-line' );
        confirmSnapshotStatus::dispatch($cloudProvider, $snapshot)->onConnection( 'booty-assembly-line')
            ->delay(now()->addMinutes(5));

        return $snapshot;
    }


    /**
     * Returns the latest snapshot for the given application
     *
     * @param String $app
     * @return void
     */
    public static function latestFor (String $app)
    {
        return self::where('status', 'Snapshot Ready')
            ->where('app', $app)
            ->whereNotNull('internal_snapshot_id')
            ->latest()
            ->first();
    }


    public function provision (String $orderId, String $orderer)
    {
        $cloudProvider = self::getCloudProvider($this->provider);

        $booty = new Booty([
            'snapshot_id' => $this->id,
            'order_id' => $orderId,
            'owner_email' =>  $orderer,
            'status' => 'Provisioning',
            'provider' =>  $this->provider,
            'size' => env('DEFAULT_INFRA_SIZE', 's-1vcpu-1gb'),
            'region' => $this->origin->region,
            'type' => env('DEFAULT_INFRA_OS_TYPE', 'ubuntu-18-04-x64'),
            'backup' => env('PROVISIONED_BOOTY_BACKUP_POLICY', true),
            'monitoring' => false,
            'sshkey' => env('DEFAULT_INFRA_SSH_KEY', '60344'),
            'app' => $this->app,
            'source_code' => $this->origin->source_code,
            'branch' => $this->origin->branch,
            'commit' => $this->origin->commit,
            'env' => env('APP_ENV')
        ]);

        $booty->save();

        orderVMProvision::dispatch($cloudProvider, $booty)->onConnection('booty-assembly-line');
        confirmVMStatus::dispatch($cloudProvider, $booty)->onConnection('booty-assembly-line')
            ->delay(now()->addSeconds(90));
        confirmVMStatus::dispatch($cloudProvider, $booty)->onConnection('booty-assembly-line')
            ->delay(now()->addSeconds(180));

        return $booty;
    }


    /**
     * Orders the cloud service provider to build a new snapshot 
     * using the latest code and then configure the application.
     *
     * @param String $sourceCode
     * @param String $commitId
     * @param String $branch
     * @param String $type
     * @param String $provider
     * @return App\Snapshot
     */
    public static function orderRefresh($sourceCode, $commitId, $branch, $type, $provider = 'DO')
    {
        $cloudProvider = self::getCloudProvider($provider);

        $image = new Snapshot([
            'name' => 'image-' . $commitId,
            'provider' => $provider,
            'resource_id' => null,
            'source_code' => $sourceCode,
            'branch' => $branch,
            'commit_id' => $commitId,
            'type' => $type,
            'env' => env('APP_ENV'),
            'status' => 'Initiated Image'
        ]);

        $image->save();

        orderImageCreate::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line');
        checkImageStatus::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
            ->delay(now()->addMinutes(12));
        orderSnapshotCreate::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
            ->delay(now()->addMinutes(15));
        checkSnapshotStatus::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
            ->delay(now()->addMinutes(20));
        orderImageDeletion::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
            ->delay(now()->addMinutes(20));

        return $image;
    }



    // public static function orderImageDelete (Snapshot $image)
    // {
    //     $cloudProvider = self::getCloudProvider($image->provider);
    //     orderImageDeletion::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line');
    // }

    public function terminate()
    {
        $cloudProvider = self::getCloudProvider($this->provider);
        
        orderSnapshotDelete::dispatch($cloudProvider, $this->internal_snapshot_id)->onConnection('booty-assembly-line');
        
        $this->status = 'Deleted';
        $this->save();

        return $this;
    }


    /**
     * Returns a service class corresponding to the cloud service selected
     *
     * @param [type] $provider
     * @return void
     */
    private static function getCloudProvider ($provider) {
        $cloudProvider = null;

        if (strtoupper($provider) ==='DO') {
             $cloudProvider = new DigitalOceanService();
        }

        return $cloudProvider;
    }
}
