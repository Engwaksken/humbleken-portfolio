<?php

namespace App\Filament\Resources;

use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function canCreate(): bool
    {
        // Tickets are opened by customers from their account.
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = SupportTicket::query()->where('status', TicketStatus::Open)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket')
                    ->schema([
                        TextEntry::make('id')->label('Ticket')->prefix('#'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('user.name')->label('Customer'),
                        TextEntry::make('user.email')->label('Email')->copyable(),
                        TextEntry::make('created_at')->label('Opened')->dateTime(),
                        TextEntry::make('last_activity_at')->label('Last activity')->dateTime(),
                    ])
                    ->columns(3),
                Section::make('Conversation')
                    ->schema([
                        RepeatableEntry::make('replies')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('user.name')
                                    ->hiddenLabel()
                                    ->weight('bold')
                                    ->placeholder('Deleted user')
                                    ->suffix(fn (TicketReply $record): string => $record->is_internal ? ' · internal note' : ''),
                                TextEntry::make('created_at')->hiddenLabel()->dateTime()->color('gray'),
                                TextEntry::make('body')->hiddenLabel()->columnSpanFull()->extraAttributes(['class' => 'whitespace-pre-line']),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('subject')->searchable()->limit(60),
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('last_activity_at')->label('Last activity')->since()->sortable(),
            ])
            ->defaultSort('last_activity_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(TicketStatus::class),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'view' => Pages\ViewSupportTicket::route('/{record}'),
        ];
    }
}
