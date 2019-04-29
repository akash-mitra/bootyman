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
    protected $order_id;

    // The --timeout value should always be at least 
    // several seconds shorter than your 
    // retry_after configuration value.
    public $timeout = 570;

    // The number of times the job may be attempted.
    public $tries = 3;

    /**
     * Create a new job instance.
     * 
     * @return void
     */
    public function __construct($cloudProvider, $resource, string $order_id, int $timeout = 570, int $tries = 3)
    {
        $this->cloudProvider = $cloudProvider;
        $this->resource = $resource;
        $this->order_id = $order_id;
        $this->timeout = $timeout;
        $this->tries = $tries;

        Journal::info('New Job Queued.', 0, get_class($this), $this->order_id, [
            'connection' => $this->connection,
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
        Journal::error(
            'Job removed from Queue due to multiple failures.',
            $exception->getCode(),
            __METHOD__,
            $this->order_id,
            [
                'connection' => $this->connection,
                'queue' => $this->queue
            ]
        );

        if (isset($this->resource->status)) {
            $this->resource->status = get_called_class() . ' Failed';
            $this->resource->save();
        }
    }
}
