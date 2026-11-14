<?php

namespace App\Notifications;

use App\Filament\Resources\EnquiryResource;
use App\Models\Enquiry;
use Illuminate\Notifications\Messages\MailMessage;

class EnquiryReceived extends AdminAlert
{
    public function __construct(public Enquiry $enquiry) {}

    public function event(): string
    {
        return 'enquiry';
    }

    public function title(): string
    {
        return 'New enquiry received';
    }

    public function summary(): string
    {
        return "From {$this->enquiry->name}";
    }

    public function url(): string
    {
        return EnquiryResource::getRecordUrl($this->enquiry);
    }

    public function icon(): string
    {
        return 'heroicon-o-envelope';
    }

    public function toMail(object $notifiable): MailMessage
    {
        // The message body stays out of email; the admin panel is the access-controlled copy.
        return (new MailMessage)
            ->subject('New enquiry received')
            ->line("A new enquiry was submitted by {$this->enquiry->name}.")
            ->action('Review enquiry', $this->url());
    }
}
