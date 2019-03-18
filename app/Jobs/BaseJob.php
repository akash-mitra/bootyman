<?php

namespace App\Jobs;

use App\Error;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $resource;
    protected $cloudProvider;
    // The --timeout value should always be at least 
    // several seconds shorter than your 
    // retry_after configuration value.
    protected $timeout;
    // The number of times the job may be attempted.
    protected $tries;

    /**
     * Create a new job instance.
     * 
     * @return void
     */
    public function __construct($cloudProvider, $resource, int $timeout = 570, int $tries = 3)
    {
        $this->cloudProvider = $cloudProvider;
        $this->resource = $resource;
        $this->timeout = $timeout;
        $this->tries = $tries;
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
        $this->resource->status = get_called_class().' Failed';
        $this->resource->save();

        $error = new Error([
            'type' => 'error',
            'provider' => '',
            'region' => '',
            'errorable_type' => 'order',
            'errorable_id' => '',
            'desc' => get_called_class().  '  Failed.' . $exception->getMessage(),
            'status' => 'unresolved',
            'token' => ''
        ]);

        $error->save();
    }
}
