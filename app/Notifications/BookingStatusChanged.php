<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\BookingRequest;
use App\Models\User;
use App\Support\NotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a signed-up customer that the status of their appointment request changed.
 */
class BookingStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BookingRequest $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof User
            ? NotificationPreferences::channels($notifiable, 'booking_update', ['mail', 'app'])
            : ['mail'];
    }

    private function status(): BookingStatus
    {
        return $this->booking->status instanceof BookingStatus ? $this->booking->status : BookingStatus::from((string) $this->booking->status);
    }

    private function headline(): string
    {
        return match ($this->status()) {
            BookingStatus::Confirmed => 'Your appointment is confirmed',
            BookingStatus::Contacted => 'I am arranging your appointment',
            BookingStatus::Completed => 'Your appointment is complete',
            BookingStatus::Declined => 'I cannot take this appointment',
            BookingStatus::New => 'Your appointment request was received',
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->headline()." ({$this->booking->reference})")
            ->line("The status of your appointment request {$this->booking->reference} is now: {$this->status()->customerLabel()}.")
            ->line('Preferred time: '.$this->booking->preferred_at?->format('j M Y, H:i'))
            ->action('View your bookings', route('account.bookings.index'));
    }

    /**
     * @return array{title: string, body: string, data: array<string, string>}
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->headline(),
            'body' => "Booking {$this->booking->reference}: {$this->status()->customerLabel()}.",
            'data' => ['route' => '/account'],
        ];
    }
}
