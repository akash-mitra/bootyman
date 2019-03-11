<?php

namespace Tests\Unit;

use Tests\APITestCase;

class orderNewSnapshotTest extends APITestCase
{
        /**
         * Check that snapshot.latest route returns a snapshot
         *
         * @return void
         */
        public function test_new_snapshot_can_be_ordered ()
        {
                $response = $this->post(route('snapshot.refresh'), [
                        'provider' => 'DO', 
                        'source_code' => 'https://github.com/akash-mitra/kayna', 
                        'branch' => 'master', 
                        'commit_id' => 'test-' . rand(1000, 9999)
                ]);

                // check the response
                $response->assertStatus(201)
                        ->assertJsonStructure([
                                'name', 'provider', 'source_code', 'branch', 'commit_id', 'status', 'env'
                ]);

                // check inside the database
                $content = $response->decodeResponseJson();
                $this->assertEquals(\App\Snapshot::find( $content['id'] )->commit_id, $content['commit_id']);
        }
}
