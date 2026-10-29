<?php

namespace App\Notifications;

use App\Filament\Resources\EnquiryResource;
use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Enquiry $enquiry) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // The message body stays out of email; the admin panel is the access-controlled copy.
        return (new MailMessage)
            ->subject('New enquiry received')
            ->line("A new enquiry was submitted by {$this->enquiry->name}.")
            ->action('Review enquiry', EnquiryResource::getUrl('view', ['record' => $this->enquiry]));
    }
}
