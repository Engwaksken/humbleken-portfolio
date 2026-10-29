<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\BookingRequestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    public function test_visitor_can_request_an_appointment_and_admins_are_notified(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->post(route('booking-requests.store'), [
            'name' => 'Ken', 'email' => 'ken@example.test', 'preferred_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'message' => 'Discuss reliability.',
        ])->assertRedirect(route('home').'#booking')->assertSessionHas('booking_sent');

        $this->assertDatabaseHas('booking_requests', ['email' => 'ken@example.test', 'status' => 'new']);
        Notification::assertSentTo($admin, BookingRequestReceived::class);
    }

    public function test_booking_request_rejects_past_dates_and_honeypot_values(): void
    {
        $this->from(route('home'))->post(route('booking-requests.store'), [
            'name' => 'Ken', 'email' => 'ken@example.test', 'preferred_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors(['preferred_at'], null, 'booking');
        $this->assertDatabaseCount('booking_requests', 0);
    }
}
