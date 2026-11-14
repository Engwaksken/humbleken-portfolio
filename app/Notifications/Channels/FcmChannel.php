<?php

namespace App\Notifications\Channels;

use App\Models\DeviceToken;
use App\Support\FirebaseMessaging;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Sends a push notification to every phone where the user is signed in to the app.
 * Skipped quietly when Firebase is not configured in admin Settings.
 */
class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm') || ! FirebaseMessaging::configured()) {
            return;
        }

        $tokens = DeviceToken::query()->where('user_id', $notifiable->getKey())->get();
        if ($tokens->isEmpty()) {
            return;
        }

        /** @var array{title: string, body: string, data?: array<string, string>} $message */
        $message = $notification->toFcm($notifiable);

        foreach ($tokens as $device) {
            try {
                $result = FirebaseMessaging::send($device->token, $message['title'], $message['body'], $message['data'] ?? []);

                if ($result === FirebaseMessaging::TOKEN_INVALID) {
                    // The app was uninstalled or the token was replaced.
                    $device->delete();
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
