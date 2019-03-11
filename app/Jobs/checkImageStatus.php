<?php

namespace App\Jobs;

use App\Snapshot;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class checkImageStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $image;
    private $cloudProvider;

    // The --timeout value should always be at least 
    // several seconds shorter than the 
    // retry_after configuration value.
    // we set this to 9 min as retry after is set to 10 min
    public $timeout = 540;

    // The number of times the job may be attempted.
    public $tries = 3;

    /**
     * Create a new job instance.
     * 
     * @return void
     */
    public function __construct($cloudProvider, Snapshot $image)
    {
        $this->cloudProvider = $cloudProvider;
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->checkImageIsPresent($this->image);
    }


    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        \Log::warn($exception->getMessage());
        $this->image->status = 'checkImageStatus Failed';
        $this->image->save();
    }
}
