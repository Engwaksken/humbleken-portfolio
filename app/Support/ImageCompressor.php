<?php

namespace App\Support;

use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

class ImageCompressor
{
    /**
     * Convert supported raster images to optimized WebP, preserving alpha where present.
     * When GD is unavailable (e.g. an incomplete local PHP setup), callers may keep the original.
     *
     * Images wider or taller than $maxDimension pixels are scaled down to fit.
     *
     * @return ?string Compressed image bytes, or null when the source is not a supported image.
     */
    public static function compress(string $path, string $mimeType, ?int $maxDimension = null): ?string
    {
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true) || ! extension_loaded('gd')) {
            return null;
        }

        static::ensureMemoryFor($path, $maxDimension);

        $contents = file_get_contents($path);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }

        try {
            if (imageistruecolor($image) === false) {
                imagepalettetotruecolor($image);
            }

            $image = static::fit($image, $maxDimension);

            imagealphablending($image, false);
            imagesavealpha($image, true);
            ob_start();

            if (! imagewebp($image, null, 82)) {
                throw new RuntimeException('The uploaded image could not be compressed.');
            }

            return ob_get_clean() ?: throw new RuntimeException('The compressed image was empty.');
        } finally {
            imagedestroy($image);
        }
    }

    public static function storeUpload(BaseFileUpload $component, TemporaryUploadedFile $file, ?int $maxDimension = null): ?string
    {
        $compressed = static::compress($file->getRealPath(), $file->getMimeType(), $maxDimension);

        if ($compressed === null) {
            return $component->saveUploadedFile($file);
        }

        $path = trim($component->getDirectory().'/'.Str::uuid().'.webp', '/');

        if (! $component->getDisk()->put($path, $compressed, ['visibility' => $component->getVisibility()])) {
            throw new RuntimeException('The uploaded image could not be stored. Please try again.');
        }

        return $path;
    }

    /**
     * Decoded images take about 5 bytes per pixel (plus a resized copy), so a phone photo can
     * exceed PHP's default 128 MB limit. Raise the limit for this request when needed.
     */
    private static function ensureMemoryFor(string $path, ?int $maxDimension): void
    {
        $size = @getimagesize($path);

        if ($size === false) {
            return;
        }

        $pixels = $size[0] * $size[1] + ($maxDimension ? $maxDimension ** 2 : 0);
        $required = memory_get_usage() + (int) ($pixels * 5 * 1.5) + 16 * 1024 * 1024;
        $limit = ini_get('memory_limit');
        $current = $limit === '-1' ? PHP_INT_MAX : static::bytes((string) $limit);

        if ($required > $current) {
            @ini_set('memory_limit', (string) (int) ceil($required / 1048576).'M');
        }
    }

    private static function bytes(string $value): int
    {
        $number = (int) $value;

        return match (strtolower(substr(trim($value), -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Scale an image down so neither side exceeds $maxDimension, keeping transparency.
     */
    private static function fit(\GdImage $image, ?int $maxDimension): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($maxDimension === null || max($width, $height) <= $maxDimension) {
            return $image;
        }

        $scale = $maxDimension / max($width, $height);
        $resized = imagecreatetruecolor(max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
