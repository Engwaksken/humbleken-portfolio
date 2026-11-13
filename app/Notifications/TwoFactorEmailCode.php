<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SensitiveParameter;

/**
 * One-time sign-in code for the admin's "email me a code" two-factor option.
 *
 * Sent immediately rather than queued (Filament's own notification is queued): the code
 * expires within minutes, and a site without a running queue worker would never send it.
 */
class TwoFactorEmailCode extends Notification
{
    use Queueable;

    public function __construct(
        #[SensitiveParameter] public string $code,
        public int $codeExpiryMinutes,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your sign-in code')
            ->greeting('Your sign-in code is '.$this->code)
            ->line('Enter this code on the sign-in page to finish signing in to the admin dashboard.')
            ->line(trans_choice('{1} The code expires in 1 minute.|[2,*] The code expires in :count minutes.', $this->codeExpiryMinutes))
            ->line('If you did not try to sign in, someone may know your password. Change it as soon as you can.');
    }
}
