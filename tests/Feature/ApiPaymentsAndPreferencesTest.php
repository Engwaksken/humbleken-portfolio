<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiPaymentsAndPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_endpoint_returns_currency_and_public_payment_details(): void
    {
        Settings::set('bank_enabled', true);
        Settings::set('bank_name', 'Stanbic Bank Uganda');
        Settings::set('bank_account_number', '9030012345678');
        Settings::set('iotec_client_secret_ciphertext', Crypt::encryptString('super-secret'));
        Settings::set('iotec_client_id', 'client-123');

        $response = $this->getJson('/api/v1/site')
            ->assertOk()
            ->assertJsonPath('data.currency.code', 'UGX')
            ->assertJsonPath('data.currency.decimals', 0)
            ->assertJsonPath('data.payments.bank.bank_name', 'Stanbic Bank Uganda')
            ->assertJsonPath('data.payments.mobile_money', null)
            ->assertJsonPath('data.payments.iotec', false);

        $this->assertStringNotContainsString('super-secret', $response->getContent());
        $this->assertStringNotContainsString('client-123', $response->getContent());
    }

    public function test_service_detail_includes_formatted_price(): void
    {
        $service = Service::factory()->create(['indicative_price' => 250000, 'is_active' => true]);

        $this->getJson("/api/v1/services/{$service->slug}")
            ->assertOk()
            ->assertJsonPath('data.indicative_price_formatted', 'UGX 250,000')
            ->assertJsonPath('data.currency', 'UGX');
    }

    public function test_customer_can_read_and_update_notification_preferences(): void
    {
        $customer = User::factory()->create();
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/auth/user')
            ->assertJsonPath('data.notification_preferences.ticket_reply.mail', true);

        $this->putJson('/api/v1/auth/notification-preferences', [
            'notification_preferences' => ['ticket_reply' => ['mail' => false], 'enquiry' => ['mail' => false]],
        ])
            ->assertOk()
            ->assertJsonPath('data.notification_preferences.ticket_reply.mail', false);

        // Unknown events are ignored, so customers cannot change admin alert settings.
        $this->assertSame(['ticket_reply' => ['mail' => false]], $customer->fresh()->notification_preferences);
    }

    public function test_notification_preferences_require_sign_in_and_valid_values(): void
    {
        $this->putJson('/api/v1/auth/notification-preferences', [])->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->putJson('/api/v1/auth/notification-preferences', ['notification_preferences' => ['ticket_reply' => ['mail' => 'maybe']]])
            ->assertUnprocessable();
    }

    public function test_submission_messages_mention_the_confirmation_email(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/booking-requests', [
            'name' => 'Sam',
            'email' => 'sam@example.test',
            'preferred_at' => now()->addDays(2)->toIso8601String(),
        ])->assertCreated()->assertJsonFragment(['message' => 'Thank you. Your appointment request has been received. I will contact you to confirm availability. A confirmation with your reference has been sent to your email.']);

        Settings::set('auto_reply_enquiry_enabled', false);
        $this->postJson('/api/v1/enquiries', [
            'name' => 'Sam',
            'email' => 'sam@example.test',
            'message' => 'My laptop will not start.',
        ])->assertCreated()->assertJsonPath('message', 'Thank you. Your enquiry has been received and will be reviewed shortly.');
    }
}
