<?php

namespace App\Filament\Resources;

use App\Enums\HeroImagePosition;
use App\Enums\PublicationStatus;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Support\ImageCompressor;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
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
use UnitEnum;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Knowledge';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Article')
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
                                    ->helperText('Used in the public address: /articles/your-slug'),
                                Textarea::make('excerpt')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->helperText('Shown in article lists and search results.'),
                            ]),
                        Tab::make('Featured image')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label('Featured image')
                                    ->disk('public')
                                    ->directory('article-images')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                                    ->maxSize(10240)
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file, maxDimension: 1600))
                                    ->helperText('Optional. Shown on the article card, at the top of the article and when the link is shared. A landscape image around 1600 × 900 px works best. Images are compressed to WebP.'),
                                TextInput::make('featured_image_alt')
                                    ->label('Image description (alt text)')
                                    ->maxLength(255)
                                    ->helperText('Describe the image for people using screen readers. Leave empty if it is purely decorative.'),
                                Radio::make('image_position')
                                    ->label('Show the image')
                                    ->options(HeroImagePosition::class)
                                    ->default(HeroImagePosition::Below->value)
                                    ->helperText('The article card always shows the image at the top.'),
                            ]),
                        Tab::make('Body')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                MarkdownEditor::make('body')
                                    ->hiddenLabel()
                                    ->helperText('Markdown. Raw HTML is removed when the page is shown.'),
                            ]),
                        Tab::make('Organisation')
                            ->icon(Heroicon::OutlinedTag)
                            ->schema([
                                Select::make('article_category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('tags')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(TagResource::fields()),
                            ]),
                        Tab::make('Publishing')
                            ->icon(Heroicon::OutlinedCalendar)
                            ->schema([
                                Select::make('status')
                                    ->options(PublicationStatus::class)
                                    ->default(PublicationStatus::Draft)
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label('Publish date')
                                    ->helperText('Leave empty to publish immediately; a future date schedules the article.'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('published_at')->label('Publish date')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(PublicationStatus::class),
                SelectFilter::make('article_category_id')->label('Category')->relationship('category', 'name'),
            ])
            ->actions([
                Action::make('viewPublic')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Article $record): string => route('articles.show', ['article' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record): bool => $record->isPublished()),
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
            'index' => Pages\ManageArticles::route('/'),
        ];
    }
}
