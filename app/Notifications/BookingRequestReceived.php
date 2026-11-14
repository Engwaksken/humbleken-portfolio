<?php

namespace App\Notifications;

use App\Filament\Resources\BookingRequestResource;
use App\Models\BookingRequest;
use Illuminate\Notifications\Messages\MailMessage;

class BookingRequestReceived extends AdminAlert
{
    public function __construct(public BookingRequest $bookingRequest) {}

    public function event(): string
    {
        return 'booking';
    }

    public function title(): string
    {
        return 'New appointment request';
    }

    public function summary(): string
    {
        return $this->bookingRequest->name.' for '.$this->bookingRequest->preferred_at->format('j M Y, H:i');
    }

    public function url(): string
    {
        return BookingRequestResource::getRecordUrl($this->bookingRequest);
    }

    public function icon(): string
    {
        return 'heroicon-o-calendar-days';
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
            ->action('Review request', $this->url());
    }
}
