<?php

namespace App\Notifications;

use App\Models\BookingRequest;
use App\Models\Enquiry;
use App\Support\AutoReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Automatic acknowledgement emailed to a client who sent an enquiry or appointment request.
 */
class ClientAutoReply extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, ?string>  $values  Placeholder values.
     */
    public function __construct(public string $type, public array $values) {}

    /**
     * Send the acknowledgement if this kind of auto reply is switched on.
     */
    public static function sendFor(Enquiry|BookingRequest $record): void
    {
        $type = $record instanceof Enquiry ? 'enquiry' : 'booking';

        if (! AutoReply::enabled($type) || blank($record->email)) {
            return;
        }

        $values = [
            'name' => $record->name,
            'email' => $record->email,
            'service' => $record->service?->name ?? '',
        ];

        if ($record instanceof BookingRequest) {
            $values += [
                'reference' => $record->reference,
                'preferred_time' => $record->preferred_at?->format('j M Y, H:i'),
                'track_url' => route('booking.track'),
            ];
        }

        NotificationFacade::route('mail', [$record->email => $record->name])
            ->notify(new static($type, $values));
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $body = AutoReply::render(AutoReply::body($this->type), $this->values);

        return (new MailMessage)
            ->subject(AutoReply::render(AutoReply::subject($this->type), $this->values))
            ->view('mail.auto-reply', ['body' => $body])
            ->text('mail.auto-reply-text', ['body' => $body]);
    }
}
