<?php

namespace App\Notifications;

use App\Filament\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells administrators that a customer opened a ticket or replied to one.
 */
class TicketActivity extends AdminAlert
{
    public function __construct(public SupportTicket $ticket, public bool $isNew) {}

    public function event(): string
    {
        return 'ticket';
    }

    public function title(): string
    {
        return $this->isNew ? "New support ticket #{$this->ticket->id}" : "Customer replied to ticket #{$this->ticket->id}";
    }

    public function summary(): string
    {
        return $this->isNew ? 'A customer opened a new support ticket.' : 'A customer replied to a support ticket.';
    }

    public function url(): string
    {
        return SupportTicketResource::getRecordUrl($this->ticket);
    }

    public function icon(): string
    {
        return 'heroicon-o-lifebuoy';
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Ticket content stays out of email; the admin panel is the access-controlled copy.
        return (new MailMessage)
            ->subject($this->title())
            ->line($this->summary())
            ->action('Open ticket', $this->url());
    }
}
