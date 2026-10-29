<?php

namespace App\Support;

use RuntimeException;

class ImageCompressor
{
    /**
     * Convert supported raster images to optimized WebP, preserving alpha where present.
     * When GD is unavailable (e.g. an incomplete local PHP setup), callers may keep the original.
     *
     * @return ?string Compressed image bytes, or null when the source is not a supported image.
     */
    public static function compress(string $path, string $mimeType): ?string
    {
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true) || ! extension_loaded('gd')) {
            return null;
        }

        $contents = file_get_contents($path);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }

        try {
            if (imageistruecolor($image) === false) {
                imagepalettetotruecolor($image);
            }

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
}
