<?php

namespace App\Jobs;

class confirmSnapshotStatus extends BaseJob
{
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->confirmSnapshot($this->resource);
    }

}
