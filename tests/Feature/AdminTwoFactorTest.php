<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filament registers the two-factor requirement on admin routes at boot,
 * so the flag must be set before the application is created.
 */
class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $this->setRequired('true');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setRequired('false');
    }

    private function setRequired(string $value): void
    {
        putenv("ADMIN_MFA_REQUIRED={$value}");
        $_ENV['ADMIN_MFA_REQUIRED'] = $_SERVER['ADMIN_MFA_REQUIRED'] = $value;
    }

    public function test_admins_without_two_factor_are_sent_to_set_it_up(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertRedirectContains('multi-factor-authentication');
        $this->actingAs($admin)->get('/admin/enquiries')->assertRedirectContains('multi-factor-authentication');
    }

    public function test_admins_with_two_factor_configured_can_use_the_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_non_admins_are_still_refused(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }
}
