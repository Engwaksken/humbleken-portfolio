<?php

namespace App\Notifications\Channels;

use App\Models\PushSubscription;
use App\Support\Settings;
use App\Support\WebPushKeys;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * Sends a browser push message to every browser the user subscribed.
 *
 * Needs the minishlink/web-push package. Without it, push is skipped quietly
 * and the other channels (email, dashboard bell) still deliver.
 */
class WebPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush') || ! WebPushKeys::libraryInstalled()) {
            return;
        }

        $subscriptions = PushSubscription::query()->where('user_id', $notifiable->getKey())->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $keys = WebPushKeys::keys();
            $webPush = new WebPush(['VAPID' => [
                'subject' => filled($email = Settings::get('contact_email')) ? "mailto:{$email}" : config('app.url'),
                'publicKey' => $keys['public'],
                'privateKey' => $keys['private'],
            ]]);

            $payload = json_encode($notification->toWebPush($notifiable));

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => 'aes128gcm',
                ]), $payload);
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    // The browser unsubscribed or the user cleared site data.
                    PushSubscription::query()->where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
                } elseif (! $report->isSuccess()) {
                    Log::warning('Push notification failed', ['reason' => $report->getReason()]);
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
