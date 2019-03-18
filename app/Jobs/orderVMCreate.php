<?php

namespace App\Jobs;

class orderVMCreate extends BaseJob
{

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
