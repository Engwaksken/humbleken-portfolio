<?php

namespace App\Filament\Pages;

use App\Support\ImageCompressor;
use App\Support\Settings as SettingsStore;
use App\Support\Theme;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;
use UnitEnum;

class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettingsForForm());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Site identity')
                            ->icon(Heroicon::OutlinedIdentification)
                            ->schema([
                                TextInput::make('brand_name')
                                    ->label('Brand name')
                                    ->default('IT Problem Solver')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('tagline')
                                    ->label('Tagline')
                                    ->maxLength(255),
                                FileUpload::make('logo')
                                    ->label('Logo')
                                    ->helperText('PNG, JPG or WebP, up to 1 MB. Upload a replacement alongside the current logo; the latest upload is saved. Raster images are compressed to WebP. SVG is not accepted.')
                                    ->disk('public')
                                    ->directory('branding')
                                    ->visibility('public')
                                    ->multiple()
                                    ->maxFiles(2)
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                                    ->maxSize(1024)
                                    ->fetchFileInformation(false)
                                    ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array => static::getStoredBrandingFile($component, $file, $storedFileNames))
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file)),
                                FileUpload::make('favicon')
                                    ->label('Favicon')
                                    ->helperText('Square PNG or ICO, up to 256 KB. Upload a replacement alongside the current favicon; the latest upload is saved. PNG images are compressed to WebP.')
                                    ->disk('public')
                                    ->directory('branding')
                                    ->visibility('public')
                                    ->multiple()
                                    ->maxFiles(2)
                                    ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                    ->maxSize(256)
                                    ->fetchFileInformation(false)
                                    ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array => static::getStoredBrandingFile($component, $file, $storedFileNames))
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file)),
                            ]),
                        Tab::make('Appearance')
                            ->icon(Heroicon::OutlinedSwatch)
                            ->schema([
                                ColorPicker::make('theme_color')
                                    ->label('Primary colour')
                                    ->hex()
                                    ->helperText('Used for links, buttons and highlights on the website and in this admin panel. Colours too light to read are rejected.')
                                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                        if (filled($value) && ($problem = Theme::problem(strtolower($value))) !== null) {
                                            $fail($problem);
                                        }
                                    }),
                                ColorPicker::make('theme_secondary_color')
                                    ->label('Secondary colour')
                                    ->hex()
                                    ->default(Theme::DEFAULT_SECONDARY_COLOR)
                                    ->helperText('Used for supporting accents. Colours too light to read are rejected.')
                                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                        if (filled($value) && ($problem = Theme::problem(strtolower($value))) !== null) {
                                            $fail($problem);
                                        }
                                    }),
                                Select::make('theme_font')
                                    ->label('Font')
                                    ->options(collect(Theme::FONTS)->map(fn (array $font): string => $font['label']))
                                    ->default('system')
                                    ->selectablePlaceholder(false)
                                    ->helperText('Downloadable fonts load from Bunny Fonts, which does not track visitors.'),
                                TextInput::make('theme_custom_font')
                                    ->label('Custom font family')
                                    ->placeholder('e.g. Arial or My Custom Font')
                                    ->maxLength(80)
                                    ->regex('/^[\pL\pN][\pL\pN \-_]{0,79}$/u')
                                    ->helperText('Overrides the preset above. Use a font installed on the visitor’s device; webfont files are not uploaded.'),
                                Radio::make('theme_font_size')
                                    ->label('Base text size')
                                    ->options(collect(Theme::SIZES)->map(fn (array $size): string => $size['label']))
                                    ->default('medium')
                                    ->inline(),
                            ]),
                        Tab::make('Contact details')
                            ->icon(Heroicon::OutlinedPhone)
                            ->columns(2)
                            ->schema([
                                TextInput::make('contact_email')
                                    ->label('Contact email')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('contact_phone')
                                    ->label('Contact phone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->maxLength(255),
                                TextInput::make('business_hours')
                                    ->label('Business hours')
                                    ->maxLength(255),
                                TextInput::make('address')
                                    ->label('Address')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Social links')
                            ->icon(Heroicon::OutlinedLink)
                            ->schema([
                                KeyValue::make('social_links')
                                    ->label('Social links')
                                    ->keyLabel('Platform')
                                    ->valueLabel('URL')
                                    ->helperText('Full web addresses starting with https://. Other links are not shown on the site.'),
                            ]),
                        Tab::make('Maintenance')
                            ->icon(Heroicon::OutlinedWrenchScrewdriver)
                            ->schema([
                                Toggle::make('maintenance_enabled')
                                    ->label('Show visitors a maintenance page')
                                    ->helperText('The admin panel keeps working, and you can still browse the site while signed in as an administrator.'),
                                Textarea::make('maintenance_message')
                                    ->label('Message for visitors')
                                    ->placeholder('We are making some improvements and will be back shortly.')
                                    ->rows(3)
                                    ->maxLength(500),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // A single FileUpload field can temporarily contain the persisted path and a
        // replacement upload together. Prefer the newly stored path for these settings.
        foreach (['logo', 'favicon'] as $key) {
            if (! is_array($state[$key] ?? null)) {
                continue;
            }

            $previous = SettingsStore::get($key);
            $paths = collect($state[$key])
                ->flatten()
                ->filter(fn (mixed $path): bool => is_string($path) && Storage::disk('public')->exists($path))
                ->values();
            $replacement = $paths->last(fn (string $path): bool => $path !== $previous);

            $state[$key] = $replacement ?? ($paths->contains($previous) ? $previous : null);

            foreach ($paths as $path) {
                if ($path !== $previous && $path !== $state[$key]) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        // Remove replaced or cleared branding files so old uploads do not linger.
        foreach (['logo', 'favicon'] as $key) {
            $path = $state[$key] ?? null;

            if (filled($path) && (! is_string($path) || ! Storage::disk('public')->exists($path))) {
                Notification::make()
                    ->danger()
                    ->title(ucfirst($key).' upload is not ready')
                    ->body('The image has not finished uploading. Please retry it and save again.')
                    ->persistent()
                    ->send();

                return;
            }

            $previous = SettingsStore::get($key);
            if (is_string($previous) && $previous !== '' && $previous !== $path) {
                Storage::disk('public')->delete($previous);
            }
        }

        foreach ($state as $key => $value) {
            SettingsStore::set($key, $value);
        }

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    /** @return array{name: string, size: int, type: string, url: string}|null */
    private static function getStoredBrandingFile(BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array
    {
        try {
            $disk = $component->getDisk();

            if (! $disk->exists($file)) {
                return null;
            }

            $contents = $disk->get($file);
            $type = $disk->mimeType($file) ?: 'application/octet-stream';
            $name = is_array($storedFileNames)
                ? ($storedFileNames[$file] ?? basename($file))
                : ($storedFileNames ?: basename($file));

            return [
                'name' => $name,
                'size' => strlen($contents),
                'type' => $type,
                'url' => 'data:'.$type.';base64,'.base64_encode($contents),
            ];
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()
                ->danger()
                ->title('Could not load saved image')
                ->body('The saved file is unavailable. Remove it, upload it again, and save settings.')
                ->persistent()
                ->send();

            return null;
        }
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getSettingsForForm(): array
    {
        $data = SettingsStore::all();

        $data['brand_name'] = $data['brand_name'] ?? 'IT Problem Solver';
        $data['theme_color'] = filled($data['theme_color'] ?? null) ? $data['theme_color'] : Theme::DEFAULT_COLOR;
        $data['theme_secondary_color'] = filled($data['theme_secondary_color'] ?? null) ? $data['theme_secondary_color'] : Theme::DEFAULT_SECONDARY_COLOR;
        $data['theme_font'] = Theme::fontKey();
        $data['theme_font_size'] = Theme::sizeKey();
        $data['maintenance_enabled'] = (bool) ($data['maintenance_enabled'] ?? false);

        foreach (['logo', 'favicon'] as $key) {
            if (filled($data[$key] ?? null) && ! Storage::disk('public')->exists($data[$key])) {
                // A stale DB path should not prevent choosing a replacement upload.
                $data[$key] = null;
            }
        }

        if (isset($data['social_links']) && is_string($data['social_links'])) {
            $data['social_links'] = json_decode($data['social_links'], true) ?? [];
        }

        return $data;
    }
}
