<?php

namespace App\Jobs;

use App\Journal;
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

        Journal::info('Queue: begin job', 0, __METHOD__, $resource->order_id, [
            'queue' => $this->queue,
            'tries' => $tries,
            'timeout' => $timeout,
        ]);
    }


    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Journal::error($exception->getMessage(), $exception->getCode(), __METHOD__, $this->resource->order_id, [
            'queue' => $this->queue
        ]);
        $this->resource->status = get_called_class() . ' Failed';
        $this->resource->save();
    }
}
