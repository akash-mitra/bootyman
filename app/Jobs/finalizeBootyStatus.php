<?php

namespace App\Jobs;


class finalizeBootyStatus extends BaseJob
{
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cloudProvider->finaliseBooty($this->resource);
    }


}
