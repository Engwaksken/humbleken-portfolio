<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use App\Notifications\TicketAnswered;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportTicket extends ViewRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-chat-bubble-left-right')
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
                ->successNotificationTitle('Reply added'),
            Action::make('changeStatus')
                ->label('Change status')
                ->color('gray')
                ->fillForm(fn (SupportTicket $record): array => ['status' => $record->status])
                ->schema([
                    Select::make('status')
                        ->options(TicketStatus::class)
                        ->required(),
                ])
                ->action(function (array $data, SupportTicket $record): void {
                    $record->status = $data['status'];
                    $record->save();
                })
                ->successNotificationTitle('Status updated'),
        ];
    }
}
