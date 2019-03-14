<?php

namespace App\Jobs;

class orderVMProvision extends BaseJob
{
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->provisionVM($this->resource);
    }

}
