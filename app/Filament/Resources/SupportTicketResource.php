<?php

namespace App\Filament\Resources;

use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Notifications\TicketAnswered;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
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
                ViewAction::make()
                    ->label('Conversation')
                    ->modalWidth(Width::ThreeExtraLarge),
                static::replyAction(),
                static::changeStatusAction(),
            ]);
    }

    public static function replyAction(): Action
    {
        return Action::make('reply')
            ->label('Reply')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->modalHeading(fn (SupportTicket $record): string => "Reply to ticket #{$record->id}")
            ->modalSubmitActionLabel('Send')
            ->schema([
                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->maxLength(10000)
                    ->rows(6),
                Toggle::make('is_internal')
                    ->label('Internal note (not shown to the customer)'),
            ])
            ->action(function (array $data, SupportTicket $record): void {
                $record->addReply(auth()->user(), $data['body'], internal: (bool) $data['is_internal']);

                if (! $data['is_internal']) {
                    $record->user->notify(new TicketAnswered($record));
                }
            })
            ->successNotificationTitle('Reply added');
    }

    public static function changeStatusAction(): Action
    {
        return Action::make('changeStatus')
            ->label('Status')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->modalHeading(fn (SupportTicket $record): string => "Change status of ticket #{$record->id}")
            ->fillForm(fn (SupportTicket $record): array => ['status' => $record->status])
            ->schema([
                Select::make('status')
                    ->options(TicketStatus::class)
                    ->required(),
            ])
            ->action(function (array $data, SupportTicket $record): void {
                // Status is not mass-assignable, so set it explicitly.
                $record->status = $data['status'];
                $record->save();
            })
            ->successNotificationTitle('Status updated');
    }

    /**
     * Admin link that opens this ticket's conversation on the list page.
     */
    public static function getRecordUrl(SupportTicket $ticket): string
    {
        return static::getUrl('index', ['tableAction' => 'view', 'tableActionRecord' => $ticket->getKey()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSupportTickets::route('/'),
        ];
    }
}
