<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\Channels\WebPushChannel;

/**
 * Which alerts a user wants, and through which channels. Stored per user in
 * users.notification_preferences as {"event": {"channel": bool}}. Missing values
 * mean "on", so existing users keep receiving email as before.
 */
class NotificationPreferences
{
    /** Alerts administrators receive. */
    public const ADMIN_EVENTS = [
        'enquiry' => 'New enquiries',
        'booking' => 'New appointment requests',
        'ticket' => 'Support ticket activity',
    ];

    /** Alerts customers receive. */
    public const CUSTOMER_EVENTS = [
        'ticket_reply' => 'Replies to my support tickets',
        'booking_update' => 'Updates to my appointment requests',
    ];

    public const ADMIN_CHANNELS = [
        'mail' => 'Email',
        'database' => 'Dashboard bell',
        'push' => 'Browser push',
    ];

    /** "app" is a push notification on phones signed in to the mobile app. */
    public const CUSTOMER_CHANNELS = [
        'mail' => 'Email',
        'app' => 'Phone notifications',
    ];

    /** The website account page only manages email; phone notifications are set in the app. */
    public const CUSTOMER_WEB_CHANNELS = [
        'mail' => 'Email',
    ];

    public static function wants(User $user, string $event, string $channel): bool
    {
        return (bool) data_get($user->notification_preferences ?? [], "{$event}.{$channel}", true);
    }

    /**
     * Laravel notification channels for this user and event.
     *
     * @return array<int, string>
     */
    public static function channels(User $user, string $event, array $available = ['mail', 'database', 'push']): array
    {
        $map = ['mail' => 'mail', 'database' => 'database', 'push' => WebPushChannel::class, 'app' => FcmChannel::class];

        return collect($available)
            ->filter(fn (string $channel): bool => static::wants($user, $event, $channel))
            ->map(fn (string $channel): string => $map[$channel])
            ->values()
            ->all();
    }

    /**
     * Form state for a user: every event and channel with its current value.
     *
     * @param  array<string, string>  $events
     * @param  array<string, string>  $channels
     * @return array<string, array<string, bool>>
     */
    public static function state(User $user, array $events, array $channels): array
    {
        $state = [];
        foreach (array_keys($events) as $event) {
            foreach (array_keys($channels) as $channel) {
                $state[$event][$channel] = static::wants($user, $event, $channel);
            }
        }

        return $state;
    }

    /**
     * Keep only known events and channels, as booleans.
     *
     * @param  array<string, string>  $events
     * @param  array<string, string>  $channels
     * @return array<string, array<string, bool>>
     */
    public static function sanitize(mixed $input, array $events, array $channels): array
    {
        $input = is_array($input) ? $input : [];
        $clean = [];
        foreach (array_keys($events) as $event) {
            foreach (array_keys($channels) as $channel) {
                $clean[$event][$channel] = (bool) data_get($input, "{$event}.{$channel}", false);
            }
        }

        return $clean;
    }
}
