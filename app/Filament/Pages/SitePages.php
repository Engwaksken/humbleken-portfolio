<?php

namespace App\Filament\Pages;

use App\Support\ImageCompressor;
use App\Support\PageContent;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;
use UnitEnum;

/**
 * Editor for the copy and images of the home page and the fixed site pages.
 */
class SitePages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $title = 'Pages';

    protected static ?string $slug = 'pages';

    protected static ?int $navigationSort = 90;

    /** Longest side, in pixels, that uploaded page images are scaled down to. */
    private const MAX_IMAGE_DIMENSION = 2000;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(PageContent::PAGES)
            ->mapWithKeys(fn (string $page): array => [$page => PageContent::raw($page)])
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pages')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Home')
                            ->icon(Heroicon::OutlinedHome)
                            ->schema($this->homeSchema()),
                        Tab::make('Services')
                            ->icon(Heroicon::OutlinedBriefcase)
                            ->schema($this->pageSchema('services')),
                        Tab::make('Approach')
                            ->icon(Heroicon::OutlinedMap)
                            ->schema($this->pageSchema('approach', steps: 'Approach steps')),
                        Tab::make('Booking')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->schema($this->pageSchema('booking', steps: '"What happens next" steps')),
                        Tab::make('Contact')
                            ->icon(Heroicon::OutlinedPhone)
                            ->schema($this->pageSchema('contact')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (PageContent::PAGES as $page) {
            PageContent::save($page, $state[$page] ?? []);
        }

        PageContent::pruneUnusedMedia();

        Notification::make()
            ->success()
            ->title('Pages saved')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save pages')
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                    Action::make('view')
                        ->label('View website')
                        ->color('gray')
                        ->url(route('home'), shouldOpenInNewTab: true),
                ])->key('form-actions'),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    private function homeSchema(): array
    {
        return [
            Section::make('Hero')
                ->description('The first thing visitors see: heading, introduction, buttons and the panel beside them.')
                ->collapsible()
                ->schema([
                    TextInput::make('home.hero_eyebrow')
                        ->label('Small heading above the title')
                        ->placeholder('Defaults to the site tagline')
                        ->maxLength(255),
                    TextInput::make('home.hero_title')->label('Title')->required()->maxLength(255),
                    Textarea::make('home.hero_intro')->label('Introduction')->rows(4)->maxLength(2000),
                    $this->introLinesInput('home.hero_intro_lines'),
                    Grid::make(2)->schema([
                        TextInput::make('home.hero_primary_label')->label('Main button text')->helperText('Links to the services page.')->required()->maxLength(60),
                        TextInput::make('home.hero_secondary_label')->label('Second button text')->helperText('Links to the booking page.')->required()->maxLength(60),
                    ]),
                    Radio::make('home.hero_aside')
                        ->label('Beside the title, show')
                        ->options(PageContent::HERO_ASIDES)
                        ->default('card')
                        ->inline()
                        ->live(),
                    $this->imageUpload('home.hero_image', 'Hero image')
                        ->visible(fn (Get $get): bool => $get('home.hero_aside') === 'image'),
                    Radio::make('home.hero_image_position')
                        ->label('Image position')
                        ->options(PageContent::HERO_IMAGE_POSITIONS)
                        ->default('right')
                        ->inline()
                        ->visible(fn (Get $get): bool => $get('home.hero_aside') === 'image'),
                    $this->altText('home.hero_image_alt')
                        ->visible(fn (Get $get): bool => $get('home.hero_aside') === 'image'),
                    Section::make('Getting-started card')
                        ->visible(fn (Get $get): bool => $get('home.hero_aside') === 'card')
                        ->schema([
                            TextInput::make('home.card_eyebrow')->label('Small heading')->maxLength(255),
                            Textarea::make('home.card_text')->label('Text')->rows(2)->maxLength(500),
                            Repeater::make('home.card_steps')
                                ->label('Numbered steps')
                                ->schema([TextInput::make('text')->hiddenLabel()->required()->maxLength(255)])
                                ->addActionLabel('Add step')
                                ->reorderable()
                                ->maxItems(6),
                            TextInput::make('home.card_link_label')->label('Link text')->helperText('Links to the contact page.')->maxLength(60),
                        ]),
                ]),
            Section::make('Services')
                ->description('Featured services, taken from the Services list in the order set there.')
                ->collapsible()
                ->schema([
                    Toggle::make('home.services_enabled')->label('Show this section')->live(),
                    Grid::make(2)
                        ->visible(fn (Get $get): bool => (bool) $get('home.services_enabled'))
                        ->schema([
                            TextInput::make('home.services_eyebrow')->label('Small heading')->maxLength(255),
                            TextInput::make('home.services_heading')->label('Heading')->required()->maxLength(255),
                            TextInput::make('home.services_link_label')->label('Link text')->maxLength(60),
                            TextInput::make('home.services_count')->label('Number of services to show')->numeric()->integer()->minValue(1)->maxValue(12)->required(),
                        ]),
                ]),
            Section::make('Why work with us')
                ->collapsible()
                ->schema([
                    Toggle::make('home.why_enabled')->label('Show this section')->live(),
                    Grid::make(1)
                        ->visible(fn (Get $get): bool => (bool) $get('home.why_enabled'))
                        ->schema([
                            TextInput::make('home.why_eyebrow')->label('Small heading')->maxLength(255),
                            TextInput::make('home.why_heading')->label('Heading')->required()->maxLength(255),
                            Repeater::make('home.why_items')
                                ->label('Reasons')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('heading')->label('Heading')->required()->maxLength(120),
                                        Select::make('icon')->label('Icon')->options(PageContent::ICONS)->default('fa-circle-check')->selectablePlaceholder(false),
                                    ]),
                                    Textarea::make('text')->label('Text')->rows(2)->maxLength(500),
                                    $this->imageUpload('image', 'Image (optional, shown instead of the icon)'),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['heading'] ?? null)
                                ->addActionLabel('Add reason')
                                ->collapsible()
                                ->reorderable()
                                ->maxItems(9),
                        ]),
                ]),
            Section::make('Recent work')
                ->description('Featured projects, taken from published Projects.')
                ->collapsible()
                ->schema([
                    Toggle::make('home.projects_enabled')->label('Show this section')->live(),
                    Grid::make(2)
                        ->visible(fn (Get $get): bool => (bool) $get('home.projects_enabled'))
                        ->schema([
                            TextInput::make('home.projects_eyebrow')->label('Small heading')->maxLength(255),
                            TextInput::make('home.projects_heading')->label('Heading')->required()->maxLength(255),
                            TextInput::make('home.projects_link_label')->label('Link text')->maxLength(60),
                            TextInput::make('home.projects_count')->label('Number of projects to show')->numeric()->integer()->minValue(1)->maxValue(12)->required(),
                        ]),
                ]),
            Section::make('Call to action')
                ->description('The closing banner with booking and contact buttons.')
                ->collapsible()
                ->schema([
                    Toggle::make('home.cta_enabled')->label('Show this section')->live(),
                    Grid::make(1)
                        ->visible(fn (Get $get): bool => (bool) $get('home.cta_enabled'))
                        ->schema([
                            TextInput::make('home.cta_title')->label('Heading')->required()->maxLength(255),
                            Textarea::make('home.cta_text')->label('Text')->rows(2)->maxLength(500),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    private function introLinesInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->label('Introduction lines shown before "Read more"')
            ->helperText('Longer introductions are shortened to this many lines with a "Read more" link. Use 0 to always show the full text.')
            ->numeric()
            ->integer()
            ->minValue(0)
            ->maxValue(10)
            ->default(PageContent::INTRO_LINES);
    }

    private function pageSchema(string $page, ?string $steps = null): array
    {
        $schema = [
            Section::make('Header')
                ->collapsible()
                ->schema([
                    TextInput::make("{$page}.eyebrow")->label('Small heading above the title')->maxLength(255),
                    TextInput::make("{$page}.title")->label('Title')->required()->maxLength(255),
                    Textarea::make("{$page}.intro")->label('Introduction')->rows(4)->maxLength(2000),
                    $this->introLinesInput("{$page}.intro_lines"),
                    $this->imageUpload("{$page}.image", 'Header image (optional)'),
                    $this->altText("{$page}.image_alt"),
                ]),
        ];

        if ($steps !== null) {
            $schema[] = Section::make($steps)
                ->collapsible()
                ->schema([
                    Repeater::make("{$page}.steps")
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('title')->label('Step title (optional)')->maxLength(120),
                            Textarea::make('text')->label('Text')->rows(2)->required()->maxLength(1000),
                            $this->imageUpload('image', 'Image (optional)'),
                        ])
                        ->itemLabel(fn (array $state): ?string => ($state['title'] ?? null) ?: str($state['text'] ?? '')->limit(60)->toString() ?: null)
                        ->addActionLabel('Add step')
                        ->collapsible()
                        ->reorderable()
                        ->maxItems(12),
                ]);
        }

        $schema[] = Section::make('More content')
            ->description('Optional text and a picture gallery shown below the main page content.')
            ->collapsible()
            ->collapsed()
            ->schema([
                Textarea::make("{$page}.body")->label('Additional text')->rows(5)->maxLength(5000),
                Repeater::make("{$page}.gallery")
                    ->label('Gallery')
                    ->schema([
                        $this->imageUpload('image', 'Image')->required(),
                        TextInput::make('caption')->label('Caption (also used as alternative text)')->maxLength(255),
                    ])
                    ->grid(2)
                    ->addActionLabel('Add image')
                    ->reorderable()
                    ->maxItems(24),
            ]);

        return $schema;
    }

    private function imageUpload(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->helperText('PNG, JPG or WebP up to 5 MB. Large images are resized and compressed to WebP.')
            ->disk('public')
            ->directory(PageContent::MEDIA_DIRECTORY)
            ->visibility('public')
            ->image()
            ->imageEditor()
            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
            ->maxSize(5120)
            ->fetchFileInformation(false)
            ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file): ?array => static::storedImage($component, $file))
            ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file, self::MAX_IMAGE_DIMENSION));
    }

    private function altText(string $name): TextInput
    {
        return TextInput::make($name)
            ->label('Image description (alternative text)')
            ->helperText('Describe the image for people using screen readers. Leave blank if it is purely decorative.')
            ->maxLength(255);
    }

    /**
     * Preview saved images through the public media route, so the admin works without a storage symlink.
     *
     * @return array{name: string, size: int, type: string, url: string}|null
     */
    private static function storedImage(BaseFileUpload $component, string $file): ?array
    {
        try {
            $disk = $component->getDisk();
            $url = PageContent::mediaUrl($file);

            if ($url === null) {
                return null;
            }

            return [
                'name' => basename($file),
                'size' => $disk->size($file),
                'type' => $disk->mimeType($file) ?: 'image/webp',
                'url' => $url,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
