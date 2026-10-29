<?php

namespace App\Filament\Pages;

use App\Support\Backups as BackupStore;
use App\Support\Settings as SettingsStore;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use UnitEnum;

class Backups extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 110;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make('Backups contain the database and uploaded files. Automatic backups run at the configured time when the Laravel scheduler is running. Remote backups use the S3-compatible disk configured in the server environment.'),
                EmbeddedTable::make(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create backup')
                ->icon(Heroicon::OutlinedPlus)
                ->requiresConfirmation()
                ->modalDescription('This may take a little while for a large site.')
                ->action(function (): void {
                    try {
                        BackupStore::create();
                    } catch (Throwable $e) {
                        report($e);
                        Notification::make()->danger()->title('Backup failed')->body($e->getMessage())->persistent()->send();

                        return;
                    }

                    Notification::make()->success()->title('Backup created')->send();
                }),
            Action::make('backupSettings')
                ->label('Automatic / remote settings')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->fillForm(fn (): array => [
                    'schedule_enabled' => (bool) SettingsStore::get('backup_schedule_enabled', false),
                    'schedule_time' => SettingsStore::get('backup_schedule_time', '02:00'),
                    'remote_enabled' => (bool) SettingsStore::get('backup_remote_enabled', false),
                    'remote_disk' => SettingsStore::get('backup_remote_disk', 's3'),
                ])
                ->schema([
                    Toggle::make('schedule_enabled')->label('Create a backup automatically each day'),
                    TimePicker::make('schedule_time')->label('Daily backup time')->seconds(false)->required(),
                    Toggle::make('remote_enabled')
                        ->label('Also store backups remotely')
                        ->helperText('Remote copies use the configured S3 storage credentials from the server environment.'),
                    Select::make('remote_disk')->label('Remote storage')->options(['s3' => 'S3-compatible storage'])->required(),
                ])
                ->action(function (array $data): void {
                    SettingsStore::set('backup_schedule_enabled', $data['schedule_enabled']);
                    SettingsStore::set('backup_schedule_time', $data['schedule_time']);
                    SettingsStore::set('backup_remote_enabled', $data['remote_enabled']);
                    SettingsStore::set('backup_remote_disk', $data['remote_disk']);

                    Notification::make()->success()->title('Backup settings saved')->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => BackupStore::all()->keyBy(fn (array $record): string => ($record['disk'] === 'local' ? '' : $record['disk'].'::').$record['name'])->all())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created')
                    ->formatStateUsing(fn (int $state): string => date('j M Y, H:i', $state)),
                TextColumn::make('name')->label('File'),
                TextColumn::make('disk')->label('Storage'),
                TextColumn::make('size')
                    ->formatStateUsing(fn (int $state): string => Number::fileSize($state, precision: 1)),
            ])
            ->emptyStateHeading('No backups yet')
            ->emptyStateDescription('Use "Create backup" to make the first one.')
            ->actions([
                Action::make('download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (array $record): StreamedResponse => Storage::disk($record['disk'])
                        ->download(BackupStore::pathFor($record['name'], $record['disk']))),
                Action::make('delete')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (array $record): string => "Delete backup {$record['name']}?")
                    ->action(function (array $record): void {
                        BackupStore::delete($record['name'], $record['disk']);
                        Notification::make()->success()->title('Backup deleted')->send();
                    }),
            ]);
    }
}
