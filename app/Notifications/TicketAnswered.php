<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a customer that staff replied to their ticket.
 */
class TicketAnswered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupportTicket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // The reply is shown after sign-in rather than sent by email.
        return (new MailMessage)
            ->subject("Update on your support ticket #{$this->ticket->id}")
            ->line('There is a new reply on your support ticket.')
            ->action('View ticket', route('account.tickets.show', $this->ticket));
    }
}
