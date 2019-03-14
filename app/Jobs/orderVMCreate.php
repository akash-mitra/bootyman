<?php

namespace App\Jobs;

class orderVMCreate extends BaseJob
{
    
    /**
     * Create a new job instance.
     * 
     * @return void
     */
    // public function __construct($cloudProvider, Snapshot $image )
    // {
    //     $this->cloudProvider = $cloudProvider;
    //     $this->image = $image;
    // }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->createVM($this->resource);
        
    }
}
