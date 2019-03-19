<?php

namespace Tests;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;


abstract class APITestCase extends TestCase
{
        use  RefreshDatabase;

        protected $user;

        protected function setUp(): void
        {
                parent::setUp();

                $this->user = factory(User::class)->create();
                Passport::actingAs($this->user);

                /**
                 * This disables the exception handling to display the stacktrace on the console
                 * the same way as it shown on the browser
                 */
                if (app()->environment('testing')) {
                        $this->withoutExceptionHandling();
                }
        }
}
