<?php

namespace App\Jobs;

use App\Booty;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class confirmDomainNameChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $booty;
    private $domainName;
    private $cloudProvider;

    // The --timeout value should always be at least 
    // several seconds shorter than the 
    // retry_after configuration value.
    // we set this to 5 min as retry after is set to 10 min
    public $timeout = 300;

    // The number of times the job may be attempted.
    public $tries = 3;

    /**
     * Create a new job instance.
     * 
     * @return void
     */
    public function __construct($cloudProvider, Booty $booty, String $domainName)
    {
        $this->cloudProvider = $cloudProvider;
        $this->domainName = $domainName;
        $this->booty = $booty;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->confirmDomainName($this->booty, $this->domainName);
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
        $this->booty->name = 'orderDomainNameChange Failed';
        $this->booty->save();
    }
}
