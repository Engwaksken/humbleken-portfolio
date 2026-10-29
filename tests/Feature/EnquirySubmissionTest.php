<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\Service;
use App\Models\User;
use App\Notifications\EnquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnquirySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Visitor',
            'email' => 'jane@example.test',
            'phone' => '',
            'message' => 'Our office network drops every afternoon.',
        ], $overrides);
    }

    public function test_contact_page_shows_the_enquiry_form(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('action="'.route('enquiries.store').'"', false)
            ->assertSee('Send enquiry');
    }

    public function test_visitor_can_submit_a_valid_enquiry(): void
    {
        $service = Service::factory()->create();

        $this->post('/enquiries', $this->validPayload(['service_id' => $service->id]))
            ->assertRedirect(route('contact'))
            ->assertSessionHas('enquiry_sent', true);

        $enquiry = Enquiry::sole();
        $this->assertSame('Jane Visitor', $enquiry->name);
        $this->assertSame($service->id, $enquiry->service_id);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertNull($enquiry->phone);
    }

    public function test_confirmation_is_shown_after_submission(): void
    {
        $this->followingRedirects()
            ->post('/enquiries', $this->validPayload())
            ->assertSee('Your enquiry has been received')
            ->assertDontSee('Send enquiry');
    }

    public function test_admins_are_notified_but_other_users_are_not(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->post('/enquiries', $this->validPayload());

        Notification::assertSentTo($admin, EnquiryReceived::class);
        Notification::assertNotSentTo($customer, EnquiryReceived::class);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->from('/')
            ->post('/enquiries', ['name' => '', 'email' => 'not-an-email', 'message' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'message'], null, 'enquiry');

        $this->assertDatabaseCount('enquiries', 0);
        Notification::assertNothingSent();
    }

    public function test_validation_errors_are_shown_next_to_fields(): void
    {
        $this->followingRedirects()
            ->from(route('contact'))
            ->post('/enquiries', $this->validPayload(['email' => 'not-an-email']))
            ->assertSee('Please correct the highlighted fields')
            ->assertSee('id="enquiry-email-error"', false)
            ->assertSee('value="Jane Visitor"', false);
    }

    public function test_inactive_or_unknown_services_are_rejected(): void
    {
        $inactive = Service::factory()->create(['is_active' => false]);

        $this->post('/enquiries', $this->validPayload(['service_id' => $inactive->id]))
            ->assertSessionHasErrors('service_id', null, 'enquiry');

        $this->post('/enquiries', $this->validPayload(['service_id' => 999999]))
            ->assertSessionHasErrors('service_id', null, 'enquiry');

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_honeypot_submissions_are_rejected(): void
    {
        $this->post('/enquiries', $this->validPayload(['website' => 'https://spam.example.test']))
            ->assertSessionHasErrors('website', null, 'enquiry');

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_visitors_cannot_set_status_or_owner(): void
    {
        $other = User::factory()->create();

        $this->post('/enquiries', $this->validPayload([
            'status' => EnquiryStatus::Closed->value,
            'user_id' => $other->id,
        ]));

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertNull($enquiry->user_id);
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/enquiries', $this->validPayload())->assertRedirect();
        }

        $this->post('/enquiries', $this->validPayload())->assertTooManyRequests();
        $this->assertDatabaseCount('enquiries', 5);
    }
}
