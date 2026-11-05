<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_password_fields_have_a_show_password_button(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('data-password-toggle', false)
            ->assertSee('aria-label="Show password"', false);

        $this->get(route('register'))->assertOk()
            ->assertSee('aria-controls="field-password"', false)
            ->assertSee('aria-controls="field-password_confirmation"', false);
    }

    public function test_account_change_password_fields_have_a_show_password_button(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('account.dashboard'))->assertOk()
            ->assertSee('aria-controls="field-current_password"', false)
            ->assertSee('aria-controls="field-password"', false)
            ->assertSee('aria-controls="field-password_confirmation"', false);
    }
}
