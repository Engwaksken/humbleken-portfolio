<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\BookingRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    private function booking(array $attributes = []): BookingRequest
    {
        return BookingRequest::create($attributes + [
            'name' => 'Ken', 'email' => 'ken@example.test', 'preferred_at' => now()->addDays(2),
        ]);
    }

    public function test_new_requests_get_a_reference_shown_after_booking(): void
    {
        $this->post(route('booking-requests.store'), [
            'name' => 'Ken', 'email' => 'ken@example.test', 'preferred_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('booking'));

        $booking = BookingRequest::sole();
        $this->assertMatchesRegularExpression('/^BK-[A-Z0-9]{8}$/', $booking->reference);
        $this->assertSame(BookingStatus::New, $booking->status);
        $this->assertNull($booking->user_id);

        $this->get(route('booking'))->assertSee($booking->reference)->assertSee(route('booking.track'), false);
    }

    public function test_signed_in_customers_requests_are_linked_to_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('booking-requests.store'), [
            'name' => 'Ken', 'email' => 'other@example.test', 'preferred_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ]);

        $this->assertSame($user->id, BookingRequest::sole()->user_id);
    }

    public function test_anyone_can_track_a_request_with_its_reference_and_email(): void
    {
        $service = Service::factory()->create(['name' => 'Network audit']);
        $booking = $this->booking(['service_id' => $service->id]);
        $booking->forceFill(['status' => BookingStatus::Confirmed, 'customer_note' => 'See you Tuesday at 10:00.'])->save();

        $this->get(route('booking.track'))->assertOk()->assertSee('Track your booking');

        $this->post(route('booking.track.lookup'), ['reference' => strtolower($booking->reference), 'email' => 'KEN@example.test'])
            ->assertOk()
            ->assertSee('Network audit')
            ->assertSee('Confirmed')
            ->assertSee('See you Tuesday at 10:00.');
    }

    public function test_tracking_needs_the_matching_email(): void
    {
        $booking = $this->booking();

        $this->post(route('booking.track.lookup'), ['reference' => $booking->reference, 'email' => 'someone@example.test'])
            ->assertOk()
            ->assertSee('We could not find a request')
            ->assertDontSee('General consultation');

        $this->post(route('booking.track.lookup'), [])->assertSessionHasErrors(['reference', 'email'], null, 'track');
    }

    public function test_customers_see_their_own_bookings_in_their_account(): void
    {
        $user = User::factory()->create(['email' => 'ken@example.test']);
        $mine = $this->booking();
        $linked = $this->booking(['email' => 'work@example.test', 'user_id' => $user->id]);
        $theirs = $this->booking(['email' => 'stranger@example.test']);

        $this->actingAs($user)->get(route('account.bookings.index'))
            ->assertOk()
            ->assertSee($mine->reference)
            ->assertSee($linked->reference)
            ->assertDontSee($theirs->reference);

        $this->actingAs($user)->get(route('account.dashboard'))->assertOk()->assertSee($mine->reference);
    }

    public function test_bookings_page_requires_sign_in(): void
    {
        $this->get(route('account.bookings.index'))->assertRedirect(route('login'));
    }

    public function test_api_returns_the_reference(): void
    {
        $this->postJson('/api/v1/booking-requests', ['name' => 'Ben', 'email' => 'ben@example.test', 'preferred_at' => now()->addDay()->toIso8601String()])
            ->assertCreated()
            ->assertJsonPath('reference', BookingRequest::sole()->reference);
    }
}
