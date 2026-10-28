<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Turning Complex IT Challenges into')
            ->assertSee('Technology that works for your business.')
            ->assertSee('Let’s make your next IT challenge simpler.')
            ->assertSee('Contact details coming soon.');
    }
}
