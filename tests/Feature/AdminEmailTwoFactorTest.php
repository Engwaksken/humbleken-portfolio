<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TwoFactorEmailCode;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEmailTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        Notification::fake();
    }

    private function admin(bool $emailCodes = true): User
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'is_admin' => true]);
        $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
        $admin->toggleEmailAuthentication($emailCodes);

        return $admin;
    }

    private function signIn(): mixed
    {
        return Livewire::test(Login::class)
            ->set('data.email', 'admin@example.test')
            ->set('data.password', 'password')
            ->call('authenticate');
    }

    public function test_admin_can_choose_to_get_a_sign_in_code_by_email(): void
    {
        $admin = $this->admin();

        // The authenticator app is offered first, so no email goes out until it is asked for.
        $login = $this->signIn()
            ->assertSee('Use a code from your authenticator app')
            ->assertSee('Send a code to your email');
        Notification::assertNothingSent();
        $this->assertGuest();

        $login->set('data.multiFactor.provider', 'email_code');

        $code = null;
        Notification::assertSentTo($admin, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code): bool {
            $code = $notification->code;

            return $notification->codeExpiryMinutes === 10;
        });

        $login->set('data.multiFactor.email_code.code', $code)->call('authenticate')->assertHasNoErrors();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_wrong_email_code_is_refused(): void
    {
        $this->admin();

        $this->signIn()
            ->set('data.multiFactor.provider', 'email_code')
            ->set('data.multiFactor.email_code.code', '000000')
            ->call('authenticate')
            ->assertHasErrors();

        $this->assertGuest();
    }

    public function test_the_option_is_not_offered_when_turned_off(): void
    {
        $this->admin(emailCodes: false);

        // Only the authenticator app is left, so there is no choice to make and no email is sent.
        $this->signIn()->assertDontSee('Send a code to your email');
        Notification::assertNothingSent();
        $this->assertGuest();
        $this->assertFalse(User::sole()->hasEmailAuthentication());
    }

    public function test_admins_can_manage_the_email_option_from_their_profile(): void
    {
        $this->actingAs($this->admin())
            ->get(Filament::getProfileUrl())
            ->assertOk()
            ->assertSee('Authenticator app')
            ->assertSee('Email verification codes');
    }

    public function test_the_code_email_is_sent_immediately_not_queued(): void
    {
        $this->assertNotInstanceOf(ShouldQueue::class, new TwoFactorEmailCode('123456', 10));

        $mail = (new TwoFactorEmailCode('123456', 10))->toMail(new User);
        $this->assertStringContainsString('123456', $mail->greeting);
        $this->assertContains('The code expires in 10 minutes.', $mail->introLines);
    }

    public function test_existing_and_new_admins_get_email_codes_turned_on(): void
    {
        $this->artisan('app:create-admin-user')
            ->expectsQuestion('Administrator name', 'New Admin')
            ->expectsQuestion('Administrator email', 'new@example.test')
            ->expectsQuestion('Password (minimum 12 characters)', 'a-long-password-123')
            ->expectsQuestion('Confirm password', 'a-long-password-123')
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'new@example.test')->sole()->hasEmailAuthentication());
    }
}
