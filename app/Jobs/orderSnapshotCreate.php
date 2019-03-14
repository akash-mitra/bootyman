<?php

namespace App\Jobs;

class orderSnapshotCreate extends BaseJob
{
   
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->createSnapshot ($this->resource);
        
    }
}
