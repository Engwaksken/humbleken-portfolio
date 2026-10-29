<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPublic')
                ->label('View on site')
                ->url(fn (Project $record): string => route('projects.show', ['project' => $record->slug]))
                ->openUrlInNewTab()
                ->visible(fn (Project $record): bool => $record->isPublished()),
            DeleteAction::make(),
        ];
    }
}
