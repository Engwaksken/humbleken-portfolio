<?php

namespace App\Support;

use App\Models\Settings as SettingsModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::rememberForever("settings.{$key}", fn () => static::model()->where('key', $key)->value('value'));

        return $value ?? $default;
    }

    /**
     * Public URL of an uploaded file stored under a setting (logo, favicon), or null if unset or missing.
     */
    public static function publicFileUrl(string $key): ?string
    {
        $path = static::get($key);

        if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function set(string $key, mixed $value): void
    {
        static::model()->updateOrCreate(['key' => $key], ['value' => static::normalize($value)]);

        Cache::forget("settings.{$key}");
    }

    public static function all(): array
    {
        return static::model()->pluck('value', 'key')->all();
    }

    public static function forget(string $key): void
    {
        static::model()->where('key', $key)->delete();

        Cache::forget("settings.{$key}");
    }

    protected static function model(): Builder
    {
        return SettingsModel::query();
    }

    protected static function normalize(mixed $value): mixed
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        if ($value === null) {
            return null;
        }

        return (string) $value;
    }
}
