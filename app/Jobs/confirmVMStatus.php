<?php

namespace App\Jobs;


class confirmVMStatus extends BaseJob
{
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->confirmVM($this->resource);
    }


}
