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
            ->assertSee('IT should make your day easier.')
            ->assertSee('The tech stuff that keeps nagging at you.')
            ->assertSee('Plain talk. Useful answers. No pressure.')
            ->assertSee('Contact details are being added.');
    }
}
