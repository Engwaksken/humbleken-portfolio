<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTicketResource\Pages\ViewSupportTicket;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketAnswered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSupportTicketResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_and_view_tickets_including_internal_notes(): void
    {
        $admin = $this->admin();
        $ticket = SupportTicket::factory()->create(['subject' => 'Email bouncing']);
        $ticket->addReply($ticket->user, 'Messages to Gmail bounce.');
        $ticket->addReply($admin, 'Check SPF record', internal: true);

        $this->actingAs($admin)->get('/admin/support-tickets')->assertOk()->assertSee('Email bouncing');
        $this->actingAs($admin)->get("/admin/support-tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('Messages to Gmail bounce.')
            ->assertSee('Check SPF record');
    }

    public function test_non_admin_and_guest_cannot_view_tickets_in_admin(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($ticket->user)->get('/admin/support-tickets')->assertForbidden();
        $this->actingAs($ticket->user)->get("/admin/support-tickets/{$ticket->id}")->assertForbidden();

        auth()->logout();
        $this->get('/admin/support-tickets')->assertRedirect('/admin/login');
    }

    public function test_admin_reply_notifies_the_customer(): void
    {
        Notification::fake();
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ViewSupportTicket::class, ['record' => $ticket->getRouteKey()])
            ->callAction('reply', data: ['body' => 'Try clearing the DNS cache.', 'is_internal' => false])
            ->assertHasNoActionErrors();

        $this->assertSame(TicketStatus::Answered, $ticket->fresh()->status);
        $this->assertDatabaseHas('ticket_replies', ['body' => 'Try clearing the DNS cache.', 'is_internal' => false]);
        Notification::assertSentTo($ticket->user, TicketAnswered::class);
    }

    public function test_internal_note_does_not_notify_the_customer(): void
    {
        Notification::fake();
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ViewSupportTicket::class, ['record' => $ticket->getRouteKey()])
            ->callAction('reply', data: ['body' => 'Escalate to vendor.', 'is_internal' => true])
            ->assertHasNoActionErrors();

        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_admin_can_close_a_ticket(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ViewSupportTicket::class, ['record' => $ticket->getRouteKey()])
            ->callAction('changeStatus', data: ['status' => TicketStatus::Closed->value])
            ->assertHasNoActionErrors();

        $this->assertSame(TicketStatus::Closed, $ticket->fresh()->status);
    }
}
