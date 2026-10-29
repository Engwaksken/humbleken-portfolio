<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Support\ImageCompressor;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServer;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Service')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Details')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Select::make('service_category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable(),
                                Textarea::make('summary')
                                    ->rows(2),
                                Textarea::make('description')
                                    ->rows(4),
                            ]),
                        Tab::make('Delivery & pricing')
                            ->icon(Heroicon::OutlinedTruck)
                            ->schema([
                                TextInput::make('delivery_method')
                                    ->maxLength(255),
                                TextInput::make('estimated_duration')
                                    ->maxLength(255),
                                TextInput::make('indicative_price')
                                    ->numeric()
                                    ->prefix('$'),
                            ]),
                        Tab::make('Samples')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                FileUpload::make('sample_image')
                                    ->label('Sample image')
                                    ->disk('public')
                                    ->directory('service-samples')
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
                                        if (filled($value) && ! Service::isAllowedSampleVideoUrl($value)) {
                                            $fail('Use a secure YouTube or Vimeo video URL.');
                                        }
                                    })
                                    ->helperText('Optional YouTube or Vimeo link; embedded using a privacy-enhanced player.'),
                            ]),
                        Tab::make('Visibility')
                            ->icon(Heroicon::OutlinedEye)
                            ->schema([
                                Toggle::make('is_active')
                                    ->default(true),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category')->sortable(),
                TextColumn::make('delivery_method'),
                TextColumn::make('estimated_duration'),
                TextColumn::make('indicative_price')->money(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make()->modalWidth(Width::ThreeExtraLarge),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
        ];
    }
}
