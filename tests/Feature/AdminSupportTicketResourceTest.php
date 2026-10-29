<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTicketResource;
use App\Filament\Resources\SupportTicketResource\Pages\ManageSupportTickets;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketAnswered;
use Filament\Actions\Testing\TestAction;
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

    public function test_admin_can_list_tickets(): void
    {
        SupportTicket::factory()->create(['subject' => 'Email bouncing']);

        $this->actingAs($this->admin())->get('/admin/support-tickets')->assertOk()->assertSee('Email bouncing');
    }

    public function test_conversation_modal_shows_replies_including_internal_notes(): void
    {
        $admin = $this->admin();
        $ticket = SupportTicket::factory()->create();
        $ticket->addReply($ticket->user, 'Messages to Gmail bounce.');
        $ticket->addReply($admin, 'Check SPF record', internal: true);

        $this->actingAs($admin);
        Livewire::test(ManageSupportTickets::class)
            ->mountAction(TestAction::make('view')->table($ticket))
            ->assertMountedActionModalSee(['Messages to Gmail bounce.', 'Check SPF record']);
    }

    public function test_admin_reply_notifies_the_customer(): void
    {
        Notification::fake();
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ManageSupportTickets::class)
            ->callAction(TestAction::make('reply')->table($ticket), data: ['body' => 'Try clearing the DNS cache.', 'is_internal' => false])
            ->assertHasNoActionErrors();

        $this->assertSame(TicketStatus::Answered, $ticket->fresh()->status);
        $this->assertDatabaseHas('ticket_replies', ['body' => 'Try clearing the DNS cache.', 'is_internal' => false]);
        Notification::assertSentTo($ticket->user, TicketAnswered::class);
    }

    public function test_reply_requires_a_message(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ManageSupportTickets::class)
            ->callAction(TestAction::make('reply')->table($ticket), data: ['body' => ''])
            ->assertHasActionErrors(['body' => 'required']);
    }

    public function test_internal_note_does_not_notify_the_customer(): void
    {
        Notification::fake();
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ManageSupportTickets::class)
            ->callAction(TestAction::make('reply')->table($ticket), data: ['body' => 'Escalate to vendor.', 'is_internal' => true])
            ->assertHasNoActionErrors();

        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_admin_can_close_a_ticket(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ManageSupportTickets::class)
            ->callAction(TestAction::make('changeStatus')->table($ticket), data: ['status' => TicketStatus::Closed->value])
            ->assertHasNoActionErrors();

        $this->assertSame(TicketStatus::Closed, $ticket->fresh()->status);
    }

    public function test_notification_link_opens_the_conversation(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->admin())->get(SupportTicketResource::getRecordUrl($ticket))->assertOk();
    }

    public function test_non_admin_and_guest_cannot_view_tickets_in_admin(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($ticket->user)->get('/admin/support-tickets')->assertForbidden();

        auth()->logout();
        $this->get('/admin/support-tickets')->assertRedirect('/admin/login');
    }
}
