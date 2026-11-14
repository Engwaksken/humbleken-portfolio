<?php

namespace App\Filament\Pages;

use App\Notifications\TestPushNotification;
use App\Support\NotificationPreferences;
use App\Support\WebPushKeys;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Each administrator chooses which alerts they receive by email, in the dashboard
 * bell and as browser push notifications.
 */
class NotificationSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $title = 'Notification settings';

    protected static ?int $navigationSort = 95;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'preferences' => NotificationPreferences::state(
                auth()->user(),
                NotificationPreferences::ADMIN_EVENTS,
                NotificationPreferences::ADMIN_CHANNELS,
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $sections = [];

        foreach (NotificationPreferences::ADMIN_EVENTS as $event => $label) {
            $toggles = [];
            foreach (NotificationPreferences::ADMIN_CHANNELS as $channel => $channelLabel) {
                $toggles[] = Toggle::make("preferences.{$event}.{$channel}")->label($channelLabel);
            }

            $sections[] = Section::make($label)
                ->columns(3)
                ->schema($toggles);
        }

        return $schema
            ->components([
                ...$sections,
                Section::make('Browser push on this device')
                    ->description('Push notifications appear on this computer or phone even when the admin panel is closed. Turn them on in each browser you use. They need the site to be served over HTTPS.')
                    ->schema([
                        View::make('filament.push-subscription')
                            ->viewData([
                                'publicKey' => rescue(fn () => WebPushKeys::publicKey(), null),
                                'libraryInstalled' => WebPushKeys::libraryInstalled(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = auth()->user();
        $existing = $user->notification_preferences ?? [];

        $user->notification_preferences = array_replace_recursive($existing, NotificationPreferences::sanitize(
            $this->form->getState()['preferences'] ?? [],
            NotificationPreferences::ADMIN_EVENTS,
            NotificationPreferences::ADMIN_CHANNELS,
        ));
        $user->save();

        Notification::make()->success()->title('Notification settings saved')->send();
    }

    public function sendTestNotification(): void
    {
        $user = auth()->user();

        Notification::make()
            ->title('Test notification')
            ->body('If you can see this in the bell, dashboard notifications are working.')
            ->icon(Heroicon::OutlinedBell)
            ->sendToDatabase($user, isEventDispatched: true);

        $user->notify(new TestPushNotification);

        Notification::make()
            ->success()
            ->title('Test sent')
            ->body('Check the bell icon, and any browsers where push is turned on.')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([$this->getFormContentComponent()]);
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save notification settings')
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                    Action::make('test')
                        ->label('Send a test notification')
                        ->color('gray')
                        ->icon(Heroicon::OutlinedPaperAirplane)
                        ->action(fn () => $this->sendTestNotification()),
                ])->key('form-actions'),
            ]);
    }
}
