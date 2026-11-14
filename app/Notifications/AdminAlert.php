<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\EmailBranding;
use App\Support\NotificationPreferences;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * An alert for administrators, delivered by email, the admin dashboard bell and
 * browser push, following each administrator's notification settings.
 */
abstract class AdminAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /** Key in NotificationPreferences::ADMIN_EVENTS. */
    abstract public function event(): string;

    abstract public function title(): string;

    /** Short, non-sensitive line shown in the bell and push message. */
    abstract public function summary(): string;

    abstract public function url(): string;

    public function icon(): string
    {
        return 'heroicon-o-bell';
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return ['mail'];
        }

        return NotificationPreferences::channels($notifiable, $this->event());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title($this->title())
            ->body($this->summary())
            ->icon($this->icon())
            // "primary" is the brand colour chosen in Settings, Appearance.
            ->iconColor('primary')
            ->actions([
                Action::make('view')
                    ->label('Open')
                    ->url($this->url())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * @return array<string, string>
     */
    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->summary(),
            'url' => $this->url(),
            'tag' => $this->event(),
            'icon' => EmailBranding::iconUrl(),
        ];
    }
}
