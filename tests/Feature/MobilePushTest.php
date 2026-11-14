<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Filament\Pages\Settings as SettingsPage;
use App\Models\BookingRequest;
use App\Models\DeviceToken;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\BookingStatusChanged;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\TicketAnswered;
use App\Support\FirebaseMessaging;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class MobilePushTest extends TestCase
{
    use RefreshDatabase;

    private function serviceAccountJson(): string
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $this->opensslConfig()]);
        openssl_pkey_export($key, $pem, null, ['config' => $this->opensslConfig()]);

        return json_encode([
            'type' => 'service_account',
            'project_id' => 'humble-ken',
            'client_email' => 'firebase-adminsdk@humble-ken.iam.gserviceaccount.com',
            'private_key' => $pem,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ]);
    }

    /** PHP on Windows needs the bundled openssl.cnf to create keys. */
    private function opensslConfig(): ?string
    {
        $path = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';

        return is_file($path) ? $path : null;
    }

    private function configureFirebase(): void
    {
        Settings::set('fcm_service_account_ciphertext', Crypt::encryptString($this->serviceAccountJson()));
    }

    public function test_customer_can_register_and_remove_a_device(): void
    {
        $customer = User::factory()->create();
        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/devices', ['token' => 'fcm-token-1', 'platform' => 'android'])->assertCreated();
        $this->postJson('/api/v1/devices', ['token' => 'fcm-token-1'])->assertCreated();
        $this->assertSame(1, $customer->deviceTokens()->count());

        $this->deleteJson('/api/v1/devices', ['token' => 'fcm-token-1'])->assertNoContent();
        $this->assertSame(0, DeviceToken::count());
    }

    public function test_device_registration_requires_sign_in(): void
    {
        $this->postJson('/api/v1/devices', ['token' => 'x'])->assertUnauthorized();
    }

    public function test_customer_notifications_include_phone_push_unless_turned_off(): void
    {
        $customer = User::factory()->create();
        $ticket = SupportTicket::factory()->for($customer)->create();

        $this->assertSame(['mail', FcmChannel::class], (new TicketAnswered($ticket))->via($customer));

        Sanctum::actingAs($customer);
        $this->putJson('/api/v1/auth/notification-preferences', [
            'notification_preferences' => ['ticket_reply' => ['app' => false]],
        ])->assertOk()->assertJsonPath('data.notification_preferences.ticket_reply.app', false)
            ->assertJsonPath('data.notification_preferences.ticket_reply.mail', true);

        $this->assertSame(['mail'], (new TicketAnswered($ticket))->via($customer->fresh()));
    }

    public function test_website_email_form_does_not_turn_off_phone_notifications(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->put(route('account.notifications.update'), []);

        $prefs = $customer->fresh()->notification_preferences;
        $this->assertFalse($prefs['ticket_reply']['mail']);
        $this->assertArrayNotHasKey('app', $prefs['ticket_reply']);
    }

    public function test_changing_a_booking_status_notifies_the_customer(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $booking = BookingRequest::create(['name' => 'C', 'email' => $customer->email, 'preferred_at' => now()->addDay()]);
        $booking->forceFill(['user_id' => $customer->id])->save();

        Notification::assertNothingSent();

        $booking->update(['message' => 'edited']);
        Notification::assertNothingSent();

        $booking->forceFill(['status' => BookingStatus::Confirmed])->save();
        Notification::assertSentTo($customer, BookingStatusChanged::class);
    }

    public function test_push_is_sent_through_firebase_and_dead_tokens_are_removed(): void
    {
        $this->configureFirebase();
        $customer = User::factory()->create();
        $customer->deviceTokens()->create(['token' => 'good-token', 'token_hash' => DeviceToken::hashOf('good-token')]);
        $customer->deviceTokens()->create(['token' => 'dead-token', 'token_hash' => DeviceToken::hashOf('dead-token')]);
        $ticket = SupportTicket::factory()->for($customer)->create();

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'google-token', 'expires_in' => 3600]),
            'fcm.googleapis.com/*' => function (Request $request) {
                return $request['message']['token'] === 'dead-token'
                    ? Http::response(['error' => ['code' => 404, 'details' => [['errorCode' => 'UNREGISTERED']]]], 404)
                    : Http::response(['name' => 'projects/humble-ken/messages/1']);
            },
        ]);

        $customer->notifyNow(new TicketAnswered($ticket), [FcmChannel::class]);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'projects/humble-ken/messages:send')
            && $request->hasHeader('Authorization', 'Bearer google-token')
            && $request['message']['notification']['title'] === "New reply on ticket #{$ticket->id}"
            && $request['message']['data']['route'] === "/account/tickets/{$ticket->id}");
        $this->assertSame(['good-token'], $customer->deviceTokens()->pluck('token')->all());
    }

    public function test_admin_can_save_and_test_the_firebase_key(): void
    {
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'google-token', 'expires_in' => 3600])]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $json = $this->serviceAccountJson();

        Livewire::test(SettingsPage::class)
            ->set('data.fcm_service_account', '{"not": "a key"}')
            ->call('save');
        $this->assertFalse(FirebaseMessaging::configured());

        Livewire::test(SettingsPage::class)
            ->set('data.fcm_service_account', $json)
            ->call('testFirebaseConnection')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(FirebaseMessaging::configured());
        $this->assertSame('humble-ken', FirebaseMessaging::serviceAccount()['project_id']);
        $this->assertNull(Settings::get('fcm_service_account'));
        $this->assertStringNotContainsString('PRIVATE KEY', (string) Settings::get('fcm_service_account_ciphertext'));
        Http::assertSent(fn (Request $request): bool => $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer');
    }
}
