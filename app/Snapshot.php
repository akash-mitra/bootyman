<?php

namespace App;

use App\Jobs\orderImageCreate;
use App\Jobs\checkImageStatus;
use App\Jobs\orderImageDeletion;
use App\Jobs\orderSnapshotCreate;
use App\Jobs\checkSnapshotStatus;
use App\Jobs\orderSnapshotDeletion;
use Illuminate\Database\Eloquent\Model;
use App\CloudProviders\DigitalOceanService;

class Snapshot extends Model
{
    protected $fillable = [ 'name', 'provider', 'resource_id', 'internal_snapshot_id', 'source_code', 'branch', 'commit_id', 'type', 'status', 'env'];

    /**
     * Orders the cloud service provider to build a new image 
     * and then configures the application using code from source control.
     *
     * @param String $sourceCode
     * @param String $commitId
     * @param String $branch
     * @param String $type
     * @param String $provider
     * @return App\Snapshot
     */
    public static function orderImage($sourceCode, $commitId, $branch, $type, $provider = 'DO') 
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

        orderImageCreate::dispatch ($cloudProvider, $image)->onConnection( 'snapshot-assembly-line' );
        checkImageStatus::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line')
            ->delay(now()->addMinutes(15));

        return $image;
    }


    /**
     * Orders the creation of a new snapshot from the image id provided.
     *
     * @param String $imageId
     * @param String $provider
     * @return void
     */
    public static function orderSnapshot (String $imageId, String $provider)
    {
        $image = Snapshot::where('resource_id', $imageId)->first();

        if ($image === null) {
            // something is severe. //TODO
            abort(404, "orderSnapshotFromImage can not find the image");
        }

        if ($image->status != 'Image Ready') {
            throw new \Exception('Image state not ready');
        }

        $cloudProvider = self::getCloudProvider($provider);

        $image->status = 'Initiated Snapshot';
        $image->save();

        orderSnapshotCreate::dispatch($cloudProvider, $image)->onConnection( 'snapshot-assembly-line' );
        checkSnapshotStatus::dispatch($cloudProvider, $image)->onConnection( 'snapshot-assembly-line')
            ->delay(now()->addMinutes(15));

        return $image;
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



    public static function orderImageDelete (Snapshot $image)
    {
        $cloudProvider = self::getCloudProvider($image->provider);
        orderImageDeletion::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line');
    }

    public static function orderSnapshotDelete(Snapshot $image)
    {
        $cloudProvider = self::getCloudProvider($image->provider);
        orderSnapshotDeletion::dispatch($cloudProvider, $image)->onConnection('snapshot-assembly-line');
    }


    public static function latestReady()
    {
        return self::where('status', 'Snapshot Ready')
                -> whereNotNull('internal_snapshot_id')
                ->latest()
                ->first();
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
