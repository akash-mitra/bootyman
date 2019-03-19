<?php

namespace Tests\Feature;

use Tests\APITestCase;
use App\Jobs\orderVMCreate;
use App\Jobs\confirmVMStatus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BootyCreationTest extends APITestCase
{
    
    public function test_booty_creation_request_response_structure()
    {
        $this->withoutEvents();
        Queue::fake();


        $response = $this->json('POST', '/api/create/booty', [
            'source_code' => 'https://github.com/akash-mitra/bootyman.git',
            'app' => 'bootyman-response-test'
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'in-progress',
                'message' => 'New booty ordered',
            ])
            ->assertJsonStructure([
                'status', 'message', 'booty'
            ]);

        $booty = $response->decodeResponseJson()['booty'];

        $this->assertEquals($booty['status'], 'Initiated');
        $this->assertEquals($booty['owner_email'], $this->user->email);
    }


    public function test_booty_creation_request_creates_db_entry () 
    {
        $this->withoutEvents();
        Queue::fake();

        $sourceCode = 'https://github.com/akash-mitra/bootyman.git';
        $appName = 'test-app';
        $order = rand(0, 10000);
        $response = $this->json('POST', '/api/create/booty', [
            'source_code' => $sourceCode,
            'app' => $appName,
            'order_id' => $order
        ]);

        $booty = $response->decodeResponseJson()['booty'];

        $booty_in_db = \App\Booty::find($booty['id']);

        $this->assertEquals($order, $booty_in_db->order_id);
        $this->assertEquals($this->user->email, $booty_in_db->owner_email);
        $this->assertEquals($appName, $booty_in_db->app);
        $this->assertEquals($sourceCode, $booty_in_db->source_code);
    }


    public function test_booty_creation_request_queues_job()
    {
        Queue::fake();

        $this->json('POST', '/api/create/booty', [
            'source_code' => 'https://github.com/akash-mitra/bootyman.git',
            'app' => 'bootyman-queue-test'
        ]);
        

        Queue::assertPushed(OrderVMCreate::class);

        Queue::assertPushed( confirmVMStatus::class);
    }
}
