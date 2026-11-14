<?php

namespace Tests\Feature;

use App\Filament\Pages\NotificationSettings;
use App\Filament\Pages\Settings as SettingsPage;
use App\Models\BookingRequest;
use App\Models\Enquiry;
use App\Models\PushSubscription;
use App\Models\Service;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\Channels\WebPushChannel;
use App\Notifications\ClientAutoReply;
use App\Notifications\EnquiryReceived;
use App\Notifications\TicketAnswered;
use App\Support\AutoReply;
use App\Support\Currency;
use App\Support\PaymentSettings;
use App\Support\Settings;
use App\Support\WebPushKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsAndPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_currency_defaults_to_ugx_without_decimals(): void
    {
        $this->assertSame('UGX', Currency::code());
        $this->assertSame('UGX 150,000', Currency::format(150000));
        $this->assertNull(Currency::format(null));
    }

    public function test_currency_follows_the_admin_setting(): void
    {
        Settings::set('currency', 'USD');

        $this->assertSame('$1,250.50', Currency::format('1250.5'));

        Settings::set('currency', 'XYZ');
        $this->assertSame('UGX', Currency::code());
    }

    public function test_service_pages_show_prices_in_the_site_currency(): void
    {
        $service = Service::factory()->create(['indicative_price' => 250000, 'is_active' => true]);

        $this->get(route('services.show', $service->slug))
            ->assertOk()
            ->assertSee('UGX 250,000');
    }

    public function test_enquiry_sends_an_automatic_reply_to_the_client(): void
    {
        Notification::fake();

        $this->post('/enquiries', [
            'name' => 'Jane Visitor',
            'email' => 'jane@example.test',
            'message' => 'My printer will not connect.',
        ])->assertRedirect();

        Notification::assertSentOnDemand(ClientAutoReply::class, function (ClientAutoReply $notification, array $channels, AnonymousNotifiable $notifiable): bool {
            return $notification->type === 'enquiry'
                && array_key_exists('jane@example.test', $notifiable->routes['mail'])
                && $notification->values['name'] === 'Jane Visitor';
        });
    }

    public function test_automatic_reply_can_be_switched_off(): void
    {
        Notification::fake();
        Settings::set('auto_reply_enquiry_enabled', false);

        $this->post('/enquiries', [
            'name' => 'Jane Visitor',
            'email' => 'jane@example.test',
            'message' => 'My printer will not connect.',
        ]);

        Notification::assertSentOnDemandTimes(ClientAutoReply::class, 0);
    }

    public function test_booking_auto_reply_includes_reference_and_bank_details(): void
    {
        Settings::set('bank_enabled', true);
        Settings::set('bank_name', 'Stanbic Bank Uganda');
        Settings::set('bank_account_number', '9030012345678');
        Settings::set('auto_reply_booking_subject', 'Got it, {name}');

        $booking = BookingRequest::create([
            'name' => 'Sam',
            'email' => 'sam@example.test',
            'preferred_at' => now()->addDays(3),
        ]);
        $mail = (new ClientAutoReply('booking', [
            'name' => 'Sam',
            'reference' => $booking->reference,
            'preferred_time' => '1 Dec 2026, 10:00',
            'track_url' => route('booking.track'),
        ]))->toMail(new AnonymousNotifiable);

        $this->assertSame('Got it, Sam', $mail->subject);
        $html = (string) $mail->render();
        $this->assertStringContainsString($booking->reference, $html);
        $this->assertStringContainsString('Stanbic Bank Uganda', $html);
        $this->assertStringContainsString('9030012345678', $html);
    }

    public function test_auto_reply_escapes_client_input_in_html(): void
    {
        $text = AutoReply::render('Hello {name}', ['name' => '<script>x</script>']);
        $html = view('mail.auto-reply', ['body' => $text])->render();

        $this->assertStringNotContainsString('<script>x</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_notification_emails_use_brand_name_colour_signature_and_footer(): void
    {
        Settings::set('brand_name', 'HumbleKen IT');
        Settings::set('theme_color', '#0f766e');
        Settings::set('owner_name', 'Ken');
        Settings::set('contact_phone', '+256 700 000000');
        Settings::set('email_footer_text', 'IT support in Kampala.');

        $html = (string) (new EnquiryReceived(Enquiry::factory()->create()))
            ->toMail(User::factory()->create(['is_admin' => true]))
            ->render();

        $this->assertStringContainsString('HumbleKen IT', $html);
        $this->assertStringContainsString('#0f766e', $html);
        $this->assertStringContainsString('Kind regards,', $html);
        $this->assertStringContainsString('Ken', $html);
        $this->assertStringContainsString('IT support in Kampala.', $html);
        $this->assertStringContainsString('+256 700 000000', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_email_signature_setting_overrides_owner_name(): void
    {
        Settings::set('owner_name', 'Ken');
        Settings::set('email_signature', 'Ken, HumbleKen IT');

        $html = (string) (new EnquiryReceived(Enquiry::factory()->create()))
            ->toMail(User::factory()->create(['is_admin' => true]))
            ->render();

        $this->assertStringContainsString('Ken, HumbleKen IT', $html);
    }

    public function test_auto_reply_uses_brand_colour_and_footer(): void
    {
        Settings::set('theme_color', '#0f766e');
        Settings::set('email_footer_text', 'IT support in Kampala.');

        $html = view('mail.auto-reply', ['body' => 'Hello'])->render();

        $this->assertStringContainsString('#0f766e', $html);
        $this->assertStringContainsString('IT support in Kampala.', $html);
    }

    public function test_bell_and_push_notifications_are_branded(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $alert = new EnquiryReceived(Enquiry::factory()->create());

        $this->assertSame('primary', $alert->toDatabase($admin)['iconColor']);
        $this->assertArrayHasKey('icon', $alert->toWebPush($admin));
    }

    public function test_admin_alerts_use_email_bell_and_push_by_default(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $enquiry = Enquiry::factory()->create();

        $this->assertSame(['mail', 'database', WebPushChannel::class], (new EnquiryReceived($enquiry))->via($admin));
    }

    public function test_admin_alert_channels_follow_preferences(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->notification_preferences = ['enquiry' => ['mail' => false, 'database' => true, 'push' => false]];
        $admin->save();

        $this->assertSame(['database'], (new EnquiryReceived(Enquiry::factory()->create()))->via($admin->fresh()));
    }

    public function test_new_enquiry_appears_in_the_admin_bell(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->notification_preferences = ['enquiry' => ['mail' => false, 'push' => false]];
        $admin->save();

        $this->post('/enquiries', [
            'name' => 'Jane Visitor',
            'email' => 'jane@example.test',
            'message' => 'Help with Wi-Fi.',
        ]);

        $this->assertSame(1, $admin->fresh()->unreadNotifications()->count());
        $this->assertSame('New enquiry received', $admin->fresh()->notifications->first()->data['title']);
    }

    public function test_admin_can_save_notification_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(NotificationSettings::class)
            ->set('data.preferences.booking.mail', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse(data_get($admin->fresh()->notification_preferences, 'booking.mail'));
        $this->assertTrue(data_get($admin->fresh()->notification_preferences, 'booking.database'));
    }

    public function test_customer_can_turn_off_ticket_reply_emails(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('account.notifications.update'), [])
            ->assertRedirect();

        $ticket = SupportTicket::factory()->for($customer)->create();
        $this->assertSame([], (new TicketAnswered($ticket))->via($customer->fresh()));

        $this->actingAs($customer)->put(route('account.notifications.update'), ['preferences' => ['ticket_reply' => ['mail' => '1']]]);
        $this->assertSame(['mail'], (new TicketAnswered($ticket))->via($customer->fresh()));
    }

    public function test_only_admins_can_register_push_subscriptions(): void
    {
        $payload = [
            'endpoint' => 'https://push.example.test/abc',
            'keys' => ['p256dh' => 'BPublicKey', 'auth' => 'authsecret'],
        ];

        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.push-subscriptions.store'), $payload)
            ->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->postJson(route('admin.push-subscriptions.store'), $payload)->assertCreated();
        $this->assertSame(1, $admin->pushSubscriptions()->count());

        $this->actingAs($admin)->deleteJson(route('admin.push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_push_keys_are_generated_once_and_stored_encrypted(): void
    {
        $public = WebPushKeys::publicKey();

        $this->assertSame(87, strlen($public));
        $this->assertSame($public, WebPushKeys::publicKey());
        $this->assertNotSame(WebPushKeys::keys()['private'], Settings::get('webpush_private_key_ciphertext'));
    }

    public function test_admin_can_save_payment_settings_with_encrypted_iotec_secret(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->set('data.currency', 'KES')
            ->set('data.iotec_enabled', true)
            ->set('data.iotec_client_id', 'client-123')
            ->set('data.iotec_wallet_id', 'wallet-456')
            ->set('data.iotec_client_secret', 'super-secret')
            ->set('data.bank_enabled', true)
            ->set('data.bank_account_number', '0123456789')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('KES', Currency::code());
        $this->assertTrue(PaymentSettings::iotecEnabled());
        $this->assertSame('super-secret', PaymentSettings::iotecClientSecret());
        $this->assertNotSame('super-secret', Settings::get('iotec_client_secret_ciphertext'));
        $this->assertNull(Settings::get('iotec_client_secret'));
        $this->assertSame('super-secret', Crypt::decryptString(Settings::get('iotec_client_secret_ciphertext')));
    }

    public function test_iotec_cannot_be_enabled_without_credentials(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->set('data.iotec_enabled', true)
            ->call('save');

        $this->assertFalse((bool) Settings::get('iotec_enabled'));
    }

    public function test_iotec_connection_test_requests_a_token(): void
    {
        Http::fake(['id.iotec.io/*' => Http::response(['access_token' => 'tok', 'expires_in' => 3600])]);

        $result = PaymentSettings::requestIotecToken(PaymentSettings::IOTEC_DEFAULT_TOKEN_URL, 'id', 'secret');

        $this->assertTrue($result['ok']);
        Http::assertSent(fn ($request): bool => $request['grant_type'] === 'client_credentials' && $request['client_id'] === 'id');
    }

    public function test_iotec_connection_test_reports_rejected_credentials(): void
    {
        Http::fake(['id.iotec.io/*' => Http::response(['error' => 'invalid_client'], 400)]);

        $result = PaymentSettings::requestIotecToken(PaymentSettings::IOTEC_DEFAULT_TOKEN_URL, 'id', 'wrong');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('rejected', $result['message']);
    }
}
