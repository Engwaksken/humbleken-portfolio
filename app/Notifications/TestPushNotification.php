<?php

namespace App\Notifications;

use App\Notifications\Channels\WebPushChannel;
use App\Support\EmailBranding;
use Illuminate\Notifications\Notification;

/**
 * Sent from Notification settings to check browser push on the admin's devices.
 */
class TestPushNotification extends Notification
{
    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * @return array<string, string>
     */
    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => 'Test notification',
            'body' => 'Browser push notifications are working.',
            'url' => url('/admin'),
            'tag' => 'test',
            'icon' => EmailBranding::iconUrl(),
        ];
    }
}
