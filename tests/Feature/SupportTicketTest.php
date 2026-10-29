<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();
    }

    public function test_customer_can_open_a_ticket_and_admins_are_notified(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->post('/account/tickets', [
            'subject' => 'VPN will not connect',
            'body' => 'Since Monday the VPN client times out.',
        ]);

        $ticket = SupportTicket::sole();
        $response->assertRedirect(route('account.tickets.show', $ticket));
        $this->assertTrue($ticket->user->is($customer));
        $this->assertSame(TicketStatus::Open, $ticket->status);
        $this->assertSame('Since Monday the VPN client times out.', $ticket->replies()->sole()->body);
        Notification::assertSentTo($admin, TicketActivity::class, fn (TicketActivity $n) => $n->isNew);
        Notification::assertNotSentTo($customer, TicketActivity::class);
    }

    public function test_ticket_input_is_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/account/tickets', ['subject' => '', 'body' => 'short'])
            ->assertSessionHasErrors(['subject', 'body']);

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_customer_cannot_set_owner_or_status_when_opening_a_ticket(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($customer)->post('/account/tickets', [
            'subject' => 'Subject',
            'body' => 'A long enough message.',
            'user_id' => $other->id,
            'status' => TicketStatus::Closed->value,
        ]);

        $ticket = SupportTicket::sole();
        $this->assertTrue($ticket->user->is($customer));
        $this->assertSame(TicketStatus::Open, $ticket->status);
    }

    public function test_customer_sees_only_their_own_tickets_in_lists(): void
    {
        $customer = User::factory()->create();
        SupportTicket::factory()->for($customer)->create(['subject' => 'My printer issue']);
        SupportTicket::factory()->create(['subject' => 'Someone else payroll issue']);

        $this->actingAs($customer)->get('/account/tickets')
            ->assertOk()
            ->assertSee('My printer issue')
            ->assertDontSee('Someone else payroll issue');

        $this->actingAs($customer)->get('/account')
            ->assertSee('My printer issue')
            ->assertDontSee('Someone else payroll issue');
    }

    public function test_customer_cannot_view_another_customers_ticket(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $ticket = SupportTicket::factory()->for($owner)->create(['subject' => 'Private matter']);

        $this->actingAs($intruder)
            ->get(route('account.tickets.show', $ticket))
            ->assertNotFound();
    }

    public function test_customer_cannot_reply_to_another_customers_ticket(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $ticket = SupportTicket::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->post(route('account.tickets.reply', $ticket), ['body' => 'Injected reply'])
            ->assertNotFound();

        $this->assertDatabaseMissing('ticket_replies', ['body' => 'Injected reply']);
        Notification::assertNothingSent();
    }

    public function test_admin_without_ownership_cannot_use_customer_ticket_pages(): void
    {
        // Admins work tickets in the admin panel; the customer area is strictly owner-only.
        $admin = User::factory()->create(['is_admin' => true]);
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($admin)->get(route('account.tickets.show', $ticket))->assertNotFound();
    }

    public function test_internal_notes_are_hidden_from_the_customer(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $ticket = SupportTicket::factory()->for($customer)->create();
        $ticket->addReply($customer, 'Customer message here');
        $ticket->addReply($admin, 'Staff only: check their firewall', internal: true);
        $ticket->addReply($admin, 'Please restart the router.');

        $this->actingAs($customer)->get(route('account.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Customer message here')
            ->assertSee('Please restart the router.')
            ->assertSee('Support team')
            ->assertDontSee('Staff only: check their firewall');
    }

    public function test_customer_reply_reopens_an_answered_ticket(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $ticket = SupportTicket::factory()->for($customer)->create(['status' => TicketStatus::Answered]);

        $this->actingAs($customer)
            ->post(route('account.tickets.reply', $ticket), ['body' => 'Still broken.'])
            ->assertRedirect(route('account.tickets.show', $ticket));

        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
        Notification::assertSentTo($admin, TicketActivity::class, fn (TicketActivity $n) => ! $n->isNew);
    }

    public function test_customer_cannot_reply_to_a_closed_ticket(): void
    {
        $customer = User::factory()->create();
        $ticket = SupportTicket::factory()->for($customer)->closed()->create();

        $this->actingAs($customer)->get(route('account.tickets.show', $ticket))
            ->assertSee('This ticket is closed.')
            ->assertDontSee('Send reply');

        $this->actingAs($customer)
            ->post(route('account.tickets.reply', $ticket), ['body' => 'Hello?'])
            ->assertForbidden();

        $this->assertSame(TicketStatus::Closed, $ticket->fresh()->status);
    }

    public function test_staff_reply_marks_ticket_answered_but_internal_note_does_not(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $ticket = SupportTicket::factory()->for($customer)->create();

        $ticket->addReply($admin, 'Internal thought', internal: true);
        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);

        $ticket->addReply($admin, 'Here is the fix.');
        $this->assertSame(TicketStatus::Answered, $ticket->fresh()->status);
    }

    public function test_unverified_customers_cannot_open_tickets(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->post('/account/tickets', ['subject' => 'Subject', 'body' => 'A long enough message.'])
            ->assertRedirect('/email/verify');

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_ticket_submissions_are_rate_limited(): void
    {
        $customer = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($customer)->post('/account/tickets', ['subject' => "Ticket {$i}", 'body' => 'A long enough message.']);
        }

        $this->actingAs($customer)
            ->post('/account/tickets', ['subject' => 'One more', 'body' => 'A long enough message.'])
            ->assertTooManyRequests();
    }

    public function test_deleting_a_ticket_removes_its_replies(): void
    {
        $reply = TicketReply::factory()->create();

        $reply->ticket->delete();

        $this->assertModelMissing($reply);
    }
}
