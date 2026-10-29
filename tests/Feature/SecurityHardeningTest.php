<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Settings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get(route('contact'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_two_factor_secrets_are_encrypted_and_hidden(): void
    {
        $user = User::factory()->create();
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
        $user->saveAppAuthenticationRecoveryCodes(['code-one', 'code-two']);

        $raw = DB::table('users')->where('id', $user->id)->first();
        $this->assertStringNotContainsString('JBSWY3DPEHPK3PXP', $raw->app_authentication_secret);
        $this->assertStringNotContainsString('code-one', $raw->app_authentication_recovery_codes);
        $this->assertSame(['code-one', 'code-two'], $user->fresh()->getAppAuthenticationRecoveryCodes());
        $this->assertArrayNotHasKey('app_authentication_secret', $user->toArray());
    }

    public function test_password_reset_does_not_reveal_whether_an_account_exists(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.test']);

        $known->assertSessionHasNoErrors();
        $unknown->assertSessionHasNoErrors();
        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_only_web_social_links_are_rendered(): void
    {
        Settings::set('social_links', [
            'linkedin' => 'https://linkedin.example.test/me',
            'evil' => 'javascript:alert(1)',
            'data' => 'data:text/html,hi',
        ]);

        $this->get(route('contact'))
            ->assertSee('https://linkedin.example.test/me', false)
            ->assertDontSee('javascript:alert', false)
            ->assertDontSee('data:text/html', false);
    }
}
