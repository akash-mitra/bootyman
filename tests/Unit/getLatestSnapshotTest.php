<?php

namespace Tests\Unit;

use Tests\APITestCase;

class getLatestSnapshotTest extends APITestCase
{   
    /**
     * Check that snapshot.latest route returns a snapshot
     *
     * @return void
     */
    public function test_latest_returns_snapshot_id()
    {
        
        $response = $this->get( route('snapshot.latest') );
        
        $response
            ->assertStatus(200)
            -> assertJsonStructure([
                'snapshot_id'
            ]);
    }
}
