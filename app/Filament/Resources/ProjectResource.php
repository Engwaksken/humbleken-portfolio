<?php

namespace App\Filament\Resources;

use App\Enums\PublicationStatus;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Support\ImageCompressor;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Project')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Details')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Used in the public address: /projects/your-slug'),
                                Textarea::make('summary')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->helperText('Shown in project lists and search results.'),
                                TagsInput::make('technologies')
                                    ->placeholder('Add a technology'),
                            ]),
                        Tab::make('Write-up')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                MarkdownEditor::make('body')
                                    ->hiddenLabel()
                                    ->helperText('Markdown. Raw HTML is removed when the page is shown.'),
                            ]),
                        Tab::make('Samples')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                FileUpload::make('sample_image')
                                    ->label('Sample image')
                                    ->disk('public')
                                    ->directory('project-samples')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                                    ->maxSize(10240)
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file))
                                    ->helperText('Optional. Images are compressed to WebP when GD is available.'),
                                TextInput::make('sample_video_url')
                                    ->label('Sample video URL')
                                    ->url()
                                    ->maxLength(2048)
                                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                        if (filled($value) && ! Project::isAllowedSampleVideoUrl($value)) {
                                            $fail('Use a secure YouTube or Vimeo video URL.');
                                        }
                                    })
                                    ->helperText('Optional YouTube or Vimeo link; embedded using a privacy-enhanced player.'),
                            ]),
                        static::publishingTab(),
                    ]),
            ]);
    }

    private static function publishingTab(): Tab
    {
        return Tab::make('Publishing')
            ->icon(Heroicon::OutlinedCalendar)
            ->schema([
                Select::make('status')
                    ->options(PublicationStatus::class)
                    ->default(PublicationStatus::Draft)
                    ->required(),
                DateTimePicker::make('published_at')
                    ->label('Publish date')
                    ->helperText('Leave empty to publish immediately; a future date schedules the project.'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('published_at')->label('Publish date')->dateTime()->sortable(),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('status')->options(PublicationStatus::class),
            ])
            ->actions([
                Action::make('viewPublic')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Project $record): string => route('projects.show', ['project' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (Project $record): bool => $record->isPublished()),
                EditAction::make()->modalWidth(Width::FiveExtraLarge),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProjects::route('/'),
        ];
    }
}
