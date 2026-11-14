<?php

namespace App\Filament\Pages;

use App\Support\AiChat;
use App\Support\AutoReply;
use App\Support\Currency;
use App\Support\ImageCompressor;
use App\Support\MailSettings;
use App\Support\PaymentSettings;
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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                                    ->helperText('PNG, JPG or WebP, up to 1 MB. Upload a replacement alongside the current logo; the latest upload is saved. Raster images are compressed to WebP, and blank margins around the logo are trimmed automatically. SVG is not accepted.')
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
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file, trimMargins: true)),
                                FileUpload::make('sticky_logo')
                                    ->label('Sticky header logo')
                                    ->helperText('Optional. Shown in the header once visitors scroll and it sticks to the top, e.g. a compact or icon-only logo. Leave empty to keep the main logo. PNG, JPG or WebP, up to 1 MB.')
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
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file, trimMargins: true)),
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
                        Tab::make('About you')
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->schema([
                                TextInput::make('owner_name')
                                    ->label('Your name')
                                    ->helperText('Shown as the author of your articles. Leave empty to use the brand name.')
                                    ->maxLength(120),
                                TextInput::make('owner_role')
                                    ->label('Your role')
                                    ->placeholder('IT Engineer and Consultant')
                                    ->maxLength(120),
                                Textarea::make('owner_bio')
                                    ->label('Short bio')
                                    ->helperText('Two or three sentences in your own words, shown in the "About the author" box under each article.')
                                    ->rows(3)
                                    ->maxLength(600),
                                FileUpload::make('owner_photo')
                                    ->label('Your photo')
                                    ->helperText('Square photo of you, at least 400 × 400 px. PNG, JPG or WebP, up to 5 MB. Shown next to your name on articles.')
                                    ->disk('public')
                                    ->directory('branding')
                                    ->visibility('public')
                                    ->multiple()
                                    ->maxFiles(2)
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                                    ->maxSize(5120)
                                    ->fetchFileInformation(false)
                                    ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array => static::getStoredBrandingFile($component, $file, $storedFileNames))
                                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => ImageCompressor::storeUpload($component, $file, maxDimension: 600)),
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
                        Tab::make('AI assistant')
                            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                            ->schema([
                                Toggle::make('ai_chat_enabled')
                                    ->label('Enable public AI chatbot')
                                    ->helperText('Configure the compatible provider URL, model, and API key below, save the settings, then enable the chatbot. The assistant answers using published website content.'),
                                TextInput::make('ai_api_base_url')
                                    ->label('OpenAI-compatible API base URL')
                                    ->url()
                                    ->placeholder('https://api.openai.com')
                                    ->maxLength(500)
                                    ->helperText('Enter the provider base URL, for example https://api.openai.com. The platform appends /v1/chat/completions; do not include that path.'),
                                TextInput::make('ai_api_model')
                                    ->label('Model name')
                                    ->placeholder('e.g. gpt-4o-mini')
                                    ->maxLength(255),
                                TextInput::make('ai_api_key')
                                    ->label('API key')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated()
                                    ->helperText(SettingsStore::get('ai_api_key_ciphertext') ? 'A key is stored encrypted. Leave blank to keep it, or enter a new key to replace it.' : 'Enter the API key from your provider. It is encrypted before storage and never sent to visitors.'),
                                Toggle::make('ai_api_key_clear')
                                    ->label('Remove stored API key')
                                    ->helperText('This disables chatbot responses until a new API key is configured.'),
                                Actions::make([
                                    Action::make('testAiConnection')
                                        ->label('Test API connection')
                                        ->action(fn () => $this->testAiConnection()),
                                ]),
                            ]),
                        Tab::make('Email')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->schema([
                                Toggle::make('mail_enabled')
                                    ->label('Send email through this SMTP server')
                                    ->helperText(fn (): string => MailSettings::describeCurrentDelivery().' Sign-up confirmations, password resets and notifications all use this setting.'),
                                TextInput::make('mail_host')
                                    ->label('SMTP server')
                                    ->placeholder('e.g. smtp.gmail.com, smtp.office365.com, mail.yourdomain.com')
                                    ->maxLength(255),
                                Select::make('mail_encryption')
                                    ->label('Encryption')
                                    ->options(MailSettings::ENCRYPTIONS)
                                    ->default('tls')
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('mail_port', $state === 'ssl' ? 465 : 587)),
                                TextInput::make('mail_port')
                                    ->label('Port')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(65535)
                                    ->default(587),
                                TextInput::make('mail_username')
                                    ->label('Username')
                                    ->helperText('Usually your full email address.')
                                    ->autocomplete('off')
                                    ->maxLength(255),
                                TextInput::make('mail_password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->autocomplete('new-password')
                                    ->dehydrated()
                                    ->helperText(SettingsStore::get('mail_password_ciphertext') ? 'A password is stored encrypted. Leave blank to keep it, or enter a new one to replace it.' : 'For Gmail or Microsoft 365 with two-step verification, use an app password. It is encrypted before storage.'),
                                Toggle::make('mail_password_clear')
                                    ->label('Remove stored password'),
                                TextInput::make('mail_from_address')
                                    ->label('Send emails from (address)')
                                    ->email()
                                    ->placeholder('e.g. no-reply@yourdomain.com')
                                    ->helperText('Use an address your SMTP account is allowed to send from, or providers may reject or spam-filter the email.')
                                    ->maxLength(255),
                                TextInput::make('mail_from_name')
                                    ->label('Send emails from (name)')
                                    ->placeholder('Defaults to the brand name')
                                    ->maxLength(255),
                                Section::make('Email branding')
                                    ->description('Every email uses your logo, primary colour and brand name from the Site identity and Appearance tabs. These options fine-tune it.')
                                    ->collapsible()
                                    ->schema([
                                        Toggle::make('email_show_logo')
                                            ->label('Show my logo at the top of emails')
                                            ->default(true)
                                            ->helperText('When off, or when no logo is uploaded, the brand name is shown in your primary colour instead.'),
                                        TextInput::make('email_signature')
                                            ->label('Sign emails as')
                                            ->placeholder('Defaults to your name from About you, or the brand name')
                                            ->helperText('Shown after "Kind regards," in notification emails.')
                                            ->maxLength(120),
                                        Textarea::make('email_footer_text')
                                            ->label('Footer text')
                                            ->placeholder('e.g. IT support and consulting in Kampala.')
                                            ->helperText('Shown above your contact details at the bottom of every email.')
                                            ->rows(2)
                                            ->maxLength(500),
                                    ]),
                                Actions::make([
                                    Action::make('sendTestEmail')
                                        ->label('Send test email')
                                        ->icon(Heroicon::OutlinedPaperAirplane)
                                        ->action(fn () => $this->sendTestEmail()),
                                ]),
                            ]),
                        Tab::make('Payments')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                Select::make('currency')
                                    ->label('Currency')
                                    ->options(Currency::options())
                                    ->default(Currency::DEFAULT)
                                    ->selectablePlaceholder(false)
                                    ->required()
                                    ->helperText('Used for service prices on the website, the app and in this admin panel.'),
                                Section::make('ioTec Pay (mobile money)')
                                    ->description('Collect MTN and Airtel mobile money through ioTec Pay. Find the client ID, client secret and wallet ID in your ioTec Pay dashboard.')
                                    ->collapsible()
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('iotec_enabled')
                                            ->label('Enable ioTec Pay')
                                            ->columnSpanFull(),
                                        TextInput::make('iotec_client_id')
                                            ->label('Client ID')
                                            ->autocomplete('off')
                                            ->maxLength(255),
                                        TextInput::make('iotec_wallet_id')
                                            ->label('Wallet ID')
                                            ->autocomplete('off')
                                            ->maxLength(255),
                                        TextInput::make('iotec_client_secret')
                                            ->label('Client secret')
                                            ->password()
                                            ->revealable()
                                            ->autocomplete('new-password')
                                            ->dehydrated()
                                            ->helperText(SettingsStore::get('iotec_client_secret_ciphertext') ? 'A secret is stored encrypted. Leave blank to keep it, or enter a new one to replace it.' : 'It is encrypted before storage.'),
                                        Toggle::make('iotec_client_secret_clear')
                                            ->label('Remove stored client secret')
                                            ->inline(false),
                                        TextInput::make('iotec_token_url')
                                            ->label('Token URL')
                                            ->url()
                                            ->placeholder(PaymentSettings::IOTEC_DEFAULT_TOKEN_URL)
                                            ->helperText('Leave empty to use the default. Change it only if ioTec gives you a different address, for example for a test account.')
                                            ->maxLength(500),
                                        TextInput::make('iotec_api_url')
                                            ->label('API base URL')
                                            ->url()
                                            ->placeholder(PaymentSettings::IOTEC_DEFAULT_API_URL)
                                            ->helperText('Leave empty to use the default.')
                                            ->maxLength(500),
                                        Actions::make([
                                            Action::make('testIotec')
                                                ->label('Test ioTec connection')
                                                ->icon(Heroicon::OutlinedSignal)
                                                ->action(fn () => $this->testIotecConnection()),
                                        ])->columnSpanFull(),
                                    ]),
                                Section::make('Bank transfer')
                                    ->description('Shown to clients in automatic replies through the {payment_details} placeholder.')
                                    ->collapsible()
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('bank_enabled')
                                            ->label('Accept bank transfers')
                                            ->columnSpanFull(),
                                        TextInput::make('bank_name')
                                            ->label('Bank name')
                                            ->placeholder('e.g. Stanbic Bank Uganda')
                                            ->maxLength(255),
                                        TextInput::make('bank_account_name')
                                            ->label('Account name')
                                            ->maxLength(255),
                                        TextInput::make('bank_account_number')
                                            ->label('Account number')
                                            ->maxLength(64),
                                        TextInput::make('bank_branch')
                                            ->label('Branch')
                                            ->maxLength(255),
                                        TextInput::make('bank_swift')
                                            ->label('SWIFT / BIC code')
                                            ->helperText('For payments from outside Uganda.')
                                            ->maxLength(20),
                                    ]),
                                Section::make('Mobile money (manual)')
                                    ->description('A number clients can send money to directly, without ioTec.')
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('mobile_money_enabled')
                                            ->label('Accept direct mobile money')
                                            ->columnSpanFull(),
                                        Select::make('mobile_money_provider')
                                            ->label('Network')
                                            ->options(['MTN Mobile Money' => 'MTN Mobile Money', 'Airtel Money' => 'Airtel Money']),
                                        TextInput::make('mobile_money_number')
                                            ->label('Number')
                                            ->tel()
                                            ->maxLength(30),
                                        TextInput::make('mobile_money_name')
                                            ->label('Registered name')
                                            ->maxLength(255),
                                    ]),
                                Textarea::make('payment_instructions')
                                    ->label('Extra payment instructions')
                                    ->placeholder('e.g. Please use your booking reference as the payment reference.')
                                    ->rows(3)
                                    ->maxLength(1000),
                            ]),
                        Tab::make('Auto replies')
                            ->icon(Heroicon::OutlinedArrowUturnLeft)
                            ->schema([
                                Text::make('Clients get these emails straight after they send an enquiry or appointment request. Placeholders you can use: '.AutoReply::PLACEHOLDERS.'.'),
                                Section::make('Enquiries')
                                    ->collapsible()
                                    ->schema([
                                        Toggle::make('auto_reply_enquiry_enabled')
                                            ->label('Send an automatic reply to new enquiries')
                                            ->default(true),
                                        TextInput::make('auto_reply_enquiry_subject')
                                            ->label('Subject')
                                            ->placeholder(AutoReply::DEFAULTS['enquiry']['subject'])
                                            ->maxLength(255),
                                        Textarea::make('auto_reply_enquiry_body')
                                            ->label('Message')
                                            ->placeholder(AutoReply::DEFAULTS['enquiry']['body'])
                                            ->helperText('Leave empty to use the text shown.')
                                            ->rows(8)
                                            ->maxLength(5000),
                                    ]),
                                Section::make('Appointment requests')
                                    ->collapsible()
                                    ->schema([
                                        Toggle::make('auto_reply_booking_enabled')
                                            ->label('Send an automatic reply to new appointment requests')
                                            ->default(true),
                                        TextInput::make('auto_reply_booking_subject')
                                            ->label('Subject')
                                            ->placeholder(AutoReply::DEFAULTS['booking']['subject'])
                                            ->maxLength(255),
                                        Textarea::make('auto_reply_booking_body')
                                            ->label('Message')
                                            ->placeholder(AutoReply::DEFAULTS['booking']['body'])
                                            ->helperText('Leave empty to use the text shown.')
                                            ->rows(10)
                                            ->maxLength(5000),
                                    ]),
                            ]),
                        Tab::make('Maintenance')
                            ->icon(Heroicon::OutlinedWrenchScrewdriver)
                            ->schema([
                                Toggle::make('maintenance_enabled')
                                    ->label('Show visitors a maintenance page')
                                    ->helperText('The admin panel keeps working, and you can still browse the site while signed in as an administrator.'),
                                Textarea::make('maintenance_message')
                                    ->label('Message for visitors')
                                    ->placeholder('I am making some improvements and the site will be back shortly.')
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

        $mailPassword = (string) ($state['mail_password'] ?? '');
        $clearMailPassword = (bool) ($state['mail_password_clear'] ?? false);
        unset($state['mail_password'], $state['mail_password_clear']);

        if (($state['mail_enabled'] ?? false) && (blank($state['mail_host'] ?? null) || blank($state['mail_from_address'] ?? null))) {
            Notification::make()
                ->danger()
                ->title('Email settings are incomplete')
                ->body('Enter the SMTP server and a "send emails from" address before enabling email delivery.')
                ->send();

            return;
        }

        $apiKey = trim((string) ($state['ai_api_key'] ?? ''));
        $clearApiKey = (bool) ($state['ai_api_key_clear'] ?? false);
        unset($state['ai_api_key'], $state['ai_api_key_clear']);

        if (($state['ai_chat_enabled'] ?? false)
            && (blank($state['ai_api_base_url'] ?? null)
                || blank($state['ai_api_model'] ?? null)
                || ($apiKey === '' && ($clearApiKey || blank(SettingsStore::get('ai_api_key_ciphertext')))))) {
            Notification::make()
                ->danger()
                ->title('AI provider configuration is incomplete')
                ->body('Set an API base URL, model, and API key before enabling the chatbot.')
                ->send();

            return;
        }

        $iotecSecret = trim((string) ($state['iotec_client_secret'] ?? ''));
        $clearIotecSecret = (bool) ($state['iotec_client_secret_clear'] ?? false);
        unset($state['iotec_client_secret'], $state['iotec_client_secret_clear']);

        if (($state['iotec_enabled'] ?? false)
            && (blank($state['iotec_client_id'] ?? null)
                || blank($state['iotec_wallet_id'] ?? null)
                || ($iotecSecret === '' && ($clearIotecSecret || blank(SettingsStore::get('iotec_client_secret_ciphertext')))))) {
            Notification::make()
                ->danger()
                ->title('ioTec Pay settings are incomplete')
                ->body('Enter the client ID, client secret and wallet ID before enabling ioTec Pay.')
                ->send();

            return;
        }

        if (($state['bank_enabled'] ?? false) && blank($state['bank_account_number'] ?? null)) {
            Notification::make()
                ->danger()
                ->title('Bank details are incomplete')
                ->body('Enter at least the account number before accepting bank transfers.')
                ->send();

            return;
        }

        if ($iotecSecret !== '') {
            SettingsStore::set('iotec_client_secret_ciphertext', Crypt::encryptString($iotecSecret));
        } elseif ($clearIotecSecret) {
            SettingsStore::forget('iotec_client_secret_ciphertext');
            $state['iotec_enabled'] = false;
        }

        if ($apiKey !== '') {
            SettingsStore::set('ai_api_key_ciphertext', Crypt::encryptString($apiKey));
        } elseif ($clearApiKey) {
            SettingsStore::forget('ai_api_key_ciphertext');
            $state['ai_chat_enabled'] = false;
        }

        if ($mailPassword !== '') {
            SettingsStore::set('mail_password_ciphertext', Crypt::encryptString($mailPassword));
        } elseif ($clearMailPassword) {
            SettingsStore::forget('mail_password_ciphertext');
        }

        // A single FileUpload field can temporarily contain the persisted path and a
        // replacement upload together. Prefer the newly stored path for these settings.
        foreach (SettingsStore::BRANDING_FILES as $key) {
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
        foreach (SettingsStore::BRANDING_FILES as $key) {
            $path = $state[$key] ?? null;

            if (filled($path) && (! is_string($path) || ! Storage::disk('public')->exists($path))) {
                Notification::make()
                    ->danger()
                    ->title(ucfirst(str_replace('_', ' ', $key)).' upload is not ready')
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

    /**
     * Send a test message to the signed-in administrator using the values currently
     * in the form, so settings can be checked before they are saved.
     */
    public function sendTestEmail(): void
    {
        $host = trim((string) ($this->data['mail_host'] ?? ''));
        $from = trim((string) ($this->data['mail_from_address'] ?? ''));
        $recipient = auth()->user()?->email;

        if (blank($host) || blank($from) || blank($recipient)) {
            Notification::make()
                ->danger()
                ->title('Email settings are incomplete')
                ->body('Enter the SMTP server and a "send emails from" address first.')
                ->send();

            return;
        }

        $mailer = MailSettings::MAILER.'_test';
        config(['mail.mailers.'.$mailer => MailSettings::mailerConfig([
            'host' => $host,
            'port' => $this->data['mail_port'] ?? null,
            'encryption' => $this->data['mail_encryption'] ?? 'tls',
            'username' => $this->data['mail_username'] ?? null,
            'password' => ((string) ($this->data['mail_password'] ?? '')) ?: MailSettings::password(),
        ])]);
        // Discard any mailer built from earlier values in this request.
        app('mail.manager')->purge($mailer);

        $fromName = trim((string) ($this->data['mail_from_name'] ?? '')) ?: (SettingsStore::get('brand_name') ?: config('app.name'));
        // Uses the branded email template, so the logo, colour and footer can be checked too.
        $html = (string) (new MailMessage)
            ->greeting('Test email')
            ->line("This is a test email from {$fromName}.")
            ->line("If you can read this, outgoing email is working. Remember to save the settings and switch on 'Send email through this SMTP server'.")
            ->action('Open the admin panel', url('/admin'))
            ->render();

        try {
            Mail::mailer($mailer)->html($html, fn ($message) => $message
                ->to($recipient)
                ->from($from, $fromName)
                ->subject("Test email from {$fromName}"));

            Notification::make()
                ->success()
                ->title('Test email sent')
                ->body("Sent to {$recipient}. Check the inbox and the spam folder.")
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('The test email could not be sent')
                ->body(static::describeMailError($exception))
                ->persistent()
                ->send();
        }
    }

    private static function describeMailError(Throwable $exception): string
    {
        $message = $exception->getMessage();

        $hint = match (true) {
            str_contains($message, 'authenticate') || str_contains($message, '535') => 'The server rejected the username or password. For Gmail or Microsoft 365, create an app password and use it here.',
            str_contains($message, 'Connection could not be established') || str_contains($message, 'timed out') => 'The server could not be reached. Check the SMTP server name, port and encryption, and that this host allows outgoing SMTP connections.',
            str_contains($message, 'certificate') || str_contains($message, 'SSL') || str_contains($message, 'TLS') => 'A secure connection could not be made. Try the other encryption option with its matching port (STARTTLS 587 or SSL/TLS 465).',
            str_contains($message, '550') || str_contains($message, '553') || str_contains($message, 'sender') => 'The server refused the sender address. Use an address your SMTP account is allowed to send from.',
            default => 'Check the server, port, encryption, username and password.',
        };

        return $hint.' Server response: '.Str::limit($message, 220);
    }

    /**
     * Ask ioTec for an access token with the values currently in the form.
     */
    public function testIotecConnection(): void
    {
        $clientId = trim((string) ($this->data['iotec_client_id'] ?? ''));
        $secret = trim((string) ($this->data['iotec_client_secret'] ?? '')) ?: PaymentSettings::iotecClientSecret();
        $tokenUrl = trim((string) ($this->data['iotec_token_url'] ?? '')) ?: PaymentSettings::IOTEC_DEFAULT_TOKEN_URL;

        if (blank($clientId) || blank($secret)) {
            Notification::make()
                ->danger()
                ->title('ioTec Pay settings are incomplete')
                ->body('Enter the client ID, and a client secret or save one first.')
                ->send();

            return;
        }

        $result = PaymentSettings::requestIotecToken($tokenUrl, $clientId, $secret);

        Notification::make()
            ->{$result['ok'] ? 'success' : 'danger'}()
            ->title($result['ok'] ? 'ioTec connection successful' : 'ioTec connection failed')
            ->body($result['message'])
            ->send();
    }

    public function testAiConnection(): void
    {
        $baseUrl = rtrim(trim((string) ($this->data['ai_api_base_url'] ?? '')), '/');
        $model = trim((string) ($this->data['ai_api_model'] ?? ''));
        $apiKey = trim((string) ($this->data['ai_api_key'] ?? '')) ?: AiChat::apiKey();

        if (blank($baseUrl) || blank($model) || blank($apiKey)) {
            Notification::make()
                ->danger()
                ->title('AI provider configuration is incomplete')
                ->body('Enter a base URL and model, and provide an API key or save one first.')
                ->send();

            return;
        }

        $url = str_ends_with($baseUrl, '/v1')
            ? $baseUrl.'/chat/completions'
            : $baseUrl.'/v1/chat/completions';

        try {
            $response = Http::acceptJson()
                ->withToken($apiKey)
                ->timeout(15)
                ->post($url, [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => 'Reply with OK.']],
                    'max_completion_tokens' => 1024,
                    'temperature' => 0,
                ]);

            if ($response->successful() && filled($response->json('choices.0.message.content'))) {
                Notification::make()
                    ->success()
                    ->title('AI API connection successful')
                    ->body("The provider responded successfully using model {$model}.")
                    ->send();

                return;
            }

            $detail = match ($response->status()) {
                401, 403 => 'Check that the API key is valid and has permission to use this model.',
                404 => 'Check the API base URL and model name.',
                429 => 'The provider rate-limited the request or the account has no available quota.',
                default => 'Check the provider URL, model name, and provider status.',
            };

            if ($response->successful()) {
                $finishReason = $response->json('choices.0.finish_reason');
                $emptyResponseDetail = $finishReason === 'length'
                    ? 'The provider accepted the request, but the model used its token budget without returning text. Try a larger completion budget or a non-reasoning model.'
                    : 'The provider accepted the request but returned no text. Check that this model supports chat completions and returns message content.';

                Notification::make()
                    ->warning()
                    ->title('AI provider reached, but no text was returned')
                    ->body($emptyResponseDetail)
                    ->send();

                return;
            }

            Notification::make()
                ->danger()
                ->title('AI API connection failed (HTTP '.$response->status().')')
                ->body($detail)
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Could not reach the AI provider')
                ->body('Check the base URL and server network access, then try again.')
                ->send();
        }
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
        $data['mail_enabled'] = (bool) ($data['mail_enabled'] ?? false);
        $data['mail_encryption'] = array_key_exists($data['mail_encryption'] ?? '', MailSettings::ENCRYPTIONS) ? $data['mail_encryption'] : 'tls';
        $data['mail_port'] = $data['mail_port'] ?? 587;
        $data['currency'] = Currency::code();
        $data['email_show_logo'] = (bool) ($data['email_show_logo'] ?? true);
        foreach (['iotec_enabled', 'bank_enabled', 'mobile_money_enabled'] as $key) {
            $data[$key] = (bool) ($data[$key] ?? false);
        }
        foreach (AutoReply::TYPES as $type) {
            $data["auto_reply_{$type}_enabled"] = AutoReply::enabled($type);
        }
        // Never send stored secrets or push keys back to the browser.
        unset(
            $data['mail_password_ciphertext'],
            $data['ai_api_key_ciphertext'],
            $data['iotec_client_secret_ciphertext'],
            $data['webpush_public_key'],
            $data['webpush_private_key_ciphertext'],
        );

        foreach (SettingsStore::BRANDING_FILES as $key) {
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
