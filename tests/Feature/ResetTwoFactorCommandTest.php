<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetTwoFactorCommandTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithTwoFactor(): User
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'is_admin' => true]);
        $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
        $admin->saveAppAuthenticationRecoveryCodes(['hashed-code-1', 'hashed-code-2']);

        return $admin;
    }

    public function test_it_turns_off_two_factor_after_confirmation(): void
    {
        $admin = $this->adminWithTwoFactor();

        $this->artisan('app:reset-two-factor', ['email' => 'ADMIN@example.test'])
            ->expectsConfirmation('Turn off two-factor sign-in for admin@example.test?', 'yes')
            ->assertSuccessful();

        $admin->refresh();
        $this->assertNull($admin->getAppAuthenticationSecret());
        $this->assertNull($admin->getAppAuthenticationRecoveryCodes());
    }

    public function test_declining_changes_nothing(): void
    {
        $admin = $this->adminWithTwoFactor();

        $this->artisan('app:reset-two-factor', ['email' => 'admin@example.test'])
            ->expectsConfirmation('Turn off two-factor sign-in for admin@example.test?', 'no')
            ->assertFailed();

        $this->assertSame('JBSWY3DPEHPK3PXP', $admin->refresh()->getAppAuthenticationSecret());
    }

    public function test_unknown_email_fails_and_force_skips_the_prompt(): void
    {
        $this->artisan('app:reset-two-factor', ['email' => 'nobody@example.test'])->assertFailed();

        $admin = $this->adminWithTwoFactor();
        $this->artisan('app:reset-two-factor', ['email' => 'admin@example.test', '--force' => true])->assertSuccessful();
        $this->assertNull($admin->refresh()->getAppAuthenticationSecret());
    }
}
