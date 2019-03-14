<?php

namespace App\Jobs;

class orderVMDelete extends BaseJob
{
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->deleteVM($this->resource);
    }

}
