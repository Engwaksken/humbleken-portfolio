<?php

namespace App\Filament\Pages;

use App\Support\Backups as BackupStore;
use BackedEnum;
use Filament\Actions\Action;
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
                Text::make('Each backup is a zip of the database and uploaded files (logo, favicon). Backups are stored privately on this server: download copies and keep them somewhere else as well, so a server failure does not take the backups with it.'),
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
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => BackupStore::all()->keyBy('name')->all())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created')
                    ->formatStateUsing(fn (int $state): string => date('j M Y, H:i', $state)),
                TextColumn::make('name')->label('File'),
                TextColumn::make('size')
                    ->formatStateUsing(fn (int $state): string => Number::fileSize($state, precision: 1)),
            ])
            ->emptyStateHeading('No backups yet')
            ->emptyStateDescription('Use "Create backup" to make the first one.')
            ->actions([
                Action::make('download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (array $record): StreamedResponse => Storage::disk(BackupStore::disk())
                        ->download(BackupStore::pathFor($record['name']))),
                Action::make('delete')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (array $record): string => "Delete backup {$record['name']}?")
                    ->action(function (array $record): void {
                        BackupStore::delete($record['name']);
                        Notification::make()->success()->title('Backup deleted')->send();
                    }),
            ]);
    }
}
