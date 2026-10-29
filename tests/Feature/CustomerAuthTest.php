<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();
    }

    public function test_auth_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
        $this->get('/register')->assertOk()->assertSee('Create an account');
        $this->get('/forgot-password')->assertOk()->assertSee('Reset your password');
        $this->actingAs(User::factory()->create())->get('/user/confirm-password')->assertOk();
    }

    public function test_visitor_can_register_and_is_asked_to_verify_email(): void
    {
        $this->post('/register', [
            'name' => 'New Customer',
            'email' => 'new@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
        ])->assertRedirect('/account');

        $user = User::query()->where('email', 'new@example.test')->sole();
        $this->assertFalse($user->is_admin);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->get('/account')->assertRedirect('/email/verify');
        $this->get('/email/verify')->assertOk()->assertSee('new@example.test');
    }

    public function test_registration_cannot_grant_admin_access(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
            'is_admin' => true,
        ]);

        $user = User::query()->where('email', 'sneaky@example.test')->sole();
        $this->assertFalse($user->is_admin);
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_registration_validates_input(): void
    {
        $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [
                'name' => "Person {$i}",
                'email' => "person{$i}@example.test",
                'password' => 'a-strong-password-123',
                'password_confirmation' => 'a-strong-password-123',
            ]);
            $this->post('/logout');
        }

        $this->post('/register', [
            'name' => 'One too many',
            'email' => 'extra@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'extra@example.test']);
    }

    public function test_verification_link_verifies_the_account(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect();

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->actingAs($user)->get('/account')->assertOk();
    }

    public function test_customer_can_sign_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'correct-password-1']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password-1'])
            ->assertRedirect('/account');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_admin_signing_in_on_the_site_is_sent_to_the_admin_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'correct-password-1']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'correct-password-1'])
            ->assertRedirect('/admin');

        $this->get('/account')->assertOk()->assertSee('Go to the admin dashboard');
    }

    public function test_customers_do_not_see_the_admin_link(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/account')
            ->assertDontSee('Go to the admin dashboard');
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertTooManyRequests();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_guests_are_sent_to_sign_in_from_account_pages(): void
    {
        $this->get('/account')->assertRedirect('/login');
        $this->get('/account/tickets')->assertRedirect('/login');
    }

    public function test_header_shows_sign_in_or_account_link(): void
    {
        $this->get('/')->assertSee('href="'.route('login').'"', false);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertSee('href="'.route('account.dashboard').'"', false)
            ->assertSee('Sign out');
    }
}
