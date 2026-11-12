<?php

namespace App\Console\Commands;

use App\Support\ImageCompressor;
use App\Support\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Crops blank margins from logos uploaded before trimming happened on upload.
 */
class TrimBrandingLogos extends Command
{
    protected $signature = 'branding:trim-logos';

    protected $description = 'Trim blank margins around the uploaded logo and sticky logo';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        foreach (['logo', 'sticky_logo'] as $key) {
            $path = Settings::get($key);

            if (! is_string($path) || $path === '' || ! $disk->exists($path)) {
                $this->line("{$key}: not uploaded, skipped.");

                continue;
            }

            $compressed = ImageCompressor::compress($disk->path($path), (string) $disk->mimeType($path), trimMargins: true);

            if ($compressed === null) {
                $this->warn("{$key}: not a PNG, JPEG or WebP image (or GD is missing), skipped.");

                continue;
            }

            $newPath = trim(dirname($path), './').'/'.Str::uuid().'.webp';
            $disk->put($newPath, $compressed, ['visibility' => 'public']);
            Settings::set($key, $newPath);
            $disk->delete($path);

            [$width, $height] = getimagesizefromstring($compressed) ?: [0, 0];
            $this->info("{$key}: trimmed to {$width}x{$height}.");
        }

        return self::SUCCESS;
    }
}
