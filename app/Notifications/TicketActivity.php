<?php

namespace App\Notifications;

use App\Filament\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells administrators that a customer opened a ticket or replied to one.
 */
class TicketActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupportTicket $ticket, public bool $isNew) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Ticket content stays out of email; the admin panel is the access-controlled copy.
        return (new MailMessage)
            ->subject($this->isNew ? "New support ticket #{$this->ticket->id}" : "Customer replied to ticket #{$this->ticket->id}")
            ->line($this->isNew ? 'A customer opened a new support ticket.' : 'A customer replied to a support ticket.')
            ->action('Open ticket', SupportTicketResource::getRecordUrl($this->ticket));
    }
}
