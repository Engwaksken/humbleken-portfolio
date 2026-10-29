<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingRequestResource\Pages;
use App\Models\BookingRequest;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingRequestResource extends Resource
{
    protected static ?string $model = BookingRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Select::make('status')->options(['new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed', 'declined' => 'Declined'])->required()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Appointment request')->schema([
                TextEntry::make('name'), TextEntry::make('email')->copyable(), TextEntry::make('phone')->placeholder('Not provided'),
                TextEntry::make('service.name')->label('Service')->placeholder('Not specified'),
                TextEntry::make('preferred_at')->label('Preferred time')->dateTime(),
                TextEntry::make('message')->placeholder('No message')->columnSpanFull(),
                TextEntry::make('status')->badge(), TextEntry::make('created_at')->label('Received')->dateTime(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('preferred_at')->label('Preferred time')->dateTime()->sortable(),
            TextColumn::make('name')->searchable(), TextColumn::make('email')->searchable(),
            TextColumn::make('service.name')->label('Service'), TextColumn::make('status')->badge()->sortable(),
        ])->defaultSort('created_at', 'desc')->actions([
            ViewAction::make()->modalWidth(Width::ThreeExtraLarge),
            EditAction::make()->label('Update status')->using(function (BookingRequest $record, array $data): BookingRequest {
                $record->status = $data['status'];
                $record->save();

                return $record;
            }),
            DeleteAction::make(),
        ]);
    }

    public static function getRecordUrl(BookingRequest $record): string
    {
        return static::getUrl('index', ['tableAction' => 'view', 'tableActionRecord' => $record->getKey()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageBookingRequests::route('/')];
    }
}
