<?php

namespace App;

use App\Booty;
use Illuminate\Http\Request;
use App\Jobs\finalizeBootyStatus;
use App\Jobs\orderVMDelete;
use App\Jobs\orderVMProvision;
use App\Jobs\orderSnapshotCreate;
use App\Jobs\orderSnapshotDelete;
use App\Jobs\confirmSnapshotStatus;
use Illuminate\Database\Eloquent\Model;
use App\CloudProviders\DigitalOceanService;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snapshot extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'app', 'provider', 'booty_id', 'internal_snapshot_id', 'status', 'env', 'order_id', 'owner_email'];


    /**
     * Returns all the booties that are created using this snapshot
     *
     * @return void
     */
    public function booties()
    {
        return $this->hasMany(Booty::class);
    }


    /**
     * Returns the booty from which this snapshot was originally created
     *
     * @return void
     */
    public function origin()
    {
        return $this->belongsTo(Booty::class, 'booty_id', 'id')
        ->withDefault([
            'source_code' => 'N/A',
            'branch' => 'N/A',
            'commit' => 'N/A',
            'name' => 'Guest Author',
        ]);
    }




    public static function rebuild(Request $request)
    {
        $booty = Booty::order($request);

        $cloudProvider = self::getCloudProvider($booty->provider, $request->order_id);

        $snapshot = new Snapshot([
            'name' => now()->format('Ymdhi') . '-',
            'app' => $request->input('app'),
            'provider' => $booty->provider,
            'booty_id' => $booty->id,
            'internal_snapshot_id' => null,
            'status' => 'Initiated',
            'env' => env('APP_ENV'),
            'order_id' => $booty->order_id,
            'owner_email' => $booty->owner_email
        ]);

        $snapshot->save();

        orderSnapshotCreate::dispatch($cloudProvider, $snapshot, $request->input('order_id'))->onConnection('booty-assembly-line')
            ->delay(now()->addMinutes(16));
        confirmSnapshotStatus::dispatch($cloudProvider, $snapshot, $request->input('order_id'))->onConnection('booty-assembly-line')
            ->delay(now()->addMinutes(19));
        orderVMDelete::dispatch($cloudProvider, $booty->internal_machine_id, $request->input('order_id'))->onConnection('booty-assembly-line')
            ->delay(now()->addMinutes(30));

        return $snapshot;
    }


    /**
     * Orders the creation of a new snapshot from the booty id provided.
     *
     * @param Request $request
     * @return App\Snapshot
     */
    public static function order(Request $request)
    {
        $booty = Booty::findOrFail($request->input('booty_id'));

        if ($booty->status != 'Live' && $booty->status != 'Booty down') {
            throw new \Exception('Can not create snapshot if source booty is not in "Live" or "Booty down" state');
        }

        $provider = empty($request->input('provider')) ? env('DEFAULT_INFRA_PROVIDER', 'DO') : $request->input('provider');
        $cloudProvider = self::getCloudProvider($provider, $request->order_id);

        $snapshot = new Snapshot([
            'name' => now()->format('Ymdhi') . '-',
            'app' => $booty->app,
            'provider' => $provider,
            'booty_id' => $booty->id,
            'internal_snapshot_id' => null,
            'status' => 'Initiated',
            'env' => env('APP_ENV'),
            'order_id' => empty($request->input('order_id')) ? 0 : $request->input('order_id'),
            'owner_email' => empty($request->input('orderer')) ? auth()->user()->email : $request->input('orderer')
        ]);

        $snapshot->save();

        orderSnapshotCreate::dispatch($cloudProvider, $snapshot, $request->input('order_id'))->onConnection('booty-assembly-line');
        confirmSnapshotStatus::dispatch($cloudProvider, $snapshot, $request->input('order_id'))->onConnection('booty-assembly-line')
            ->delay(now()->addMinutes(5));

        return $snapshot;
    }


    /**
     * Returns the latest snapshot for the given application
     *
     * @param String $app
     * @return void
     */
    public static function latestFor(String $app)
    {
        return self::where('status', 'Snapshot Ready')
            ->where('app', $app)
            ->whereNotNull('internal_snapshot_id')
            ->latest()
            ->firstOrFail();
    }


    /**
     * Provisions a new VM from this snapshot
     *
     * @param String $orderId
     * @param String $orderer
     * @return void
     */
    public function provision(String $orderId, String $orderer, $services = null)
    {
        $cloudProvider = self::getCloudProvider($this->provider, $orderId);

        $booty = new Booty([
            'snapshot_id' => $this->id,
            'order_id' => $orderId,
            'owner_email' =>  $orderer,
            'status' => 'Provisioning',
            'provider' =>  $this->provider,
            'size' => config('services.infra.size'),
            'region' => $this->origin->region,
            'type' => config('services.infra.os'),
            'backup' => env('PROVISIONED_BOOTY_BACKUP_POLICY', true),
            'monitoring' => false,
            'sshkey' => config('services.infra.sshkey'),
            'app' => $this->app,
            'source_code' => $this->origin->source_code,
            'branch' => $this->origin->branch,
            'commit' => $this->origin->commit,
            'env' => env('APP_ENV'),
            'services' => json_encode($services)
        ]);

        $booty->save();

        orderVMProvision::dispatch($cloudProvider, $booty, $orderId)->onConnection('booty-provision-line');
        finalizeBootyStatus::dispatch($cloudProvider, $booty, $orderId)->onConnection('booty-provision-line')
            ->delay(now()->addSeconds(90));
        finalizeBootyStatus::dispatch($cloudProvider, $booty, $orderId)->onConnection('booty-provision-line')
            ->delay(now()->addSeconds(180));

        return $booty;
    }


    // /**
    //  * Orders the cloud service provider to build a new snapshot 
    //  * using the latest code and then configure the application.
    //  *
    //  * @param String $sourceCode
    //  * @param String $commitId
    //  * @param String $branch
    //  * @param String $type
    //  * @param String $provider
    //  * @return App\Snapshot
    //  */
    // public static function orderRefresh($sourceCode, $commitId, $branch, $type, $provider = 'DO')
    // {
    //     $cloudProvider = self::getCloudProvider($provider);

    //     $image = new Snapshot([
    //         'name' => 'image-' . $commitId,
    //         'provider' => $provider,
    //         'resource_id' => null,
    //         'source_code' => $sourceCode,
    //         'branch' => $branch,
    //         'commit_id' => $commitId,
    //         'type' => $type,
    //         'env' => env('APP_ENV'),
    //         'status' => 'Initiated Image'
    //     ]);

    //     $image->save();

    //     orderImageCreate::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line');
    //     checkImageStatus::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
    //         ->delay(now()->addMinutes(12));
    //     orderSnapshotCreate::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
    //         ->delay(now()->addMinutes(15));
    //     checkSnapshotStatus::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
    //         ->delay(now()->addMinutes(20));
    //     orderImageDeletion::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
    //         ->delay(now()->addMinutes(20));

    //     return $image;
    // }



    /**
     * Deletes the underlying image corresponding to this snapshot 
     * and updates the status of the snapshot as deleted.
     *
     * @return void
     */
    public function terminate($request)
    {
        $cloudProvider = self::getCloudProvider($this->provider, $request->order_id);

        orderSnapshotDelete::dispatch($cloudProvider, $this->internal_snapshot_id, $request->input('order_id'))->onConnection('booty-assembly-line');

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
    private static function getCloudProvider($provider, $order_id = null)
    {
        $cloudProvider = null;

        if (strtoupper($provider) === 'DO') {
            $cloudProvider = new DigitalOceanService();
            $cloudProvider->setOrderId($order_id);
        }

        return $cloudProvider;
    }
}
