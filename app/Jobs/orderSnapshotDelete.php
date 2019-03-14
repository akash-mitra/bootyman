<?php

namespace App\Jobs;

class orderSnapshotDelete extends BaseJob
{
 
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->deleteSnapshot($this->resource);
    }

}
