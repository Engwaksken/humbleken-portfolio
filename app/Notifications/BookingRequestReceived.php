<?php

namespace App\Notifications;

use App\Filament\Resources\BookingRequestResource;
use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BookingRequest $bookingRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New appointment request')
            ->greeting('A new appointment request was received.')
            ->line('From: '.$this->bookingRequest->name.' ('.$this->bookingRequest->email.')')
            ->line('Preferred time: '.$this->bookingRequest->preferred_at->format('j M Y, H:i'))
            ->line('Service: '.($this->bookingRequest->service?->name ?? 'Not specified'))
            ->line('Message: '.($this->bookingRequest->message ?: 'None provided'))
            ->action('Review request', BookingRequestResource::getRecordUrl($this->bookingRequest));
    }
}
