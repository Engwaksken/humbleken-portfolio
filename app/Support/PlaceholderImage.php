<?php

namespace App\Support;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generates simple featured images for sample content: a diagonal gradient in the site's
 * brand colours, soft shapes and a large Font Awesome icon for the topic. Nothing is
 * downloaded, so seeding works offline and the images carry no licensing questions.
 */
class PlaceholderImage
{
    public const WIDTH = 1600;

    public const HEIGHT = 900;

    /**
     * Create an image on the public disk and return its path, or null when GD cannot do it.
     *
     * @param  string  $icon  Font Awesome solid icon code point, e.g. "f6ff" for network-wired.
     * @param  string  $seed  Varies the decoration so each image looks different.
     */
    public static function create(string $directory, string $icon, string $seed): ?string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagewebp')) {
            return null;
        }

        mt_srand(crc32($seed));

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($image, true);

        $from = static::rgb(rescue(fn (): string => Theme::secondaryColor(), Theme::DEFAULT_SECONDARY_COLOR, report: false));
        $to = static::rgb(rescue(fn (): string => Theme::color(), Theme::DEFAULT_COLOR, report: false));
        static::gradient($image, $from, $to);
        static::decorate($image);
        static::icon($image, $icon);

        ob_start();
        imagewebp($image, null, 82);
        $contents = (string) ob_get_clean();
        imagedestroy($image);
        mt_srand();

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';

        return $contents !== '' && Storage::disk('public')->put($path, $contents, ['visibility' => 'public']) ? $path : null;
    }

    /**
     * Diagonal gradient, drawn as one line per diagonal so it stays fast in PHP.
     *
     * @param  array{int, int, int}  $from
     * @param  array{int, int, int}  $to
     */
    private static function gradient(GdImage $image, array $from, array $to): void
    {
        $steps = self::WIDTH + self::HEIGHT;

        for ($d = 0; $d < $steps; $d++) {
            $t = $d / ($steps - 1);
            $color = imagecolorallocate(
                $image,
                (int) round($from[0] + ($to[0] - $from[0]) * $t),
                (int) round($from[1] + ($to[1] - $from[1]) * $t),
                (int) round($from[2] + ($to[2] - $from[2]) * $t),
            );
            imageline($image, $d, 0, $d - self::HEIGHT, self::HEIGHT, $color);
        }
    }

    private static function decorate(GdImage $image): void
    {
        // Large soft circles.
        for ($i = 0; $i < 4; $i++) {
            $size = mt_rand(360, 900);
            imagefilledellipse($image, mt_rand(-200, self::WIDTH + 200), mt_rand(-200, self::HEIGHT + 200), $size, $size, imagecolorallocatealpha($image, 255, 255, 255, mt_rand(112, 120)));
        }

        // A dot grid in one corner and a few circuit-like lines.
        $dot = imagecolorallocatealpha($image, 255, 255, 255, 95);
        $startX = mt_rand(0, 1) ? 80 : self::WIDTH - 420;
        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 6; $y++) {
                imagefilledellipse($image, $startX + $x * 42, 80 + $y * 42, 7, 7, $dot);
            }
        }

        imagesetthickness($image, 3);
        $line = imagecolorallocatealpha($image, 255, 255, 255, 100);
        for ($i = 0; $i < 5; $i++) {
            $y = mt_rand(120, self::HEIGHT - 120);
            $x = mt_rand(0, 600);
            $length = mt_rand(220, 520);
            imageline($image, $x, $y, $x + $length, $y, $line);
            imageline($image, $x + $length, $y, $x + $length + 60, $y - 60, $line);
            imagefilledellipse($image, $x + $length + 60, $y - 60, 16, 16, $line);
        }
        imagesetthickness($image, 1);
    }

    private static function icon(GdImage $image, string $icon): void
    {
        $font = static::iconFont();

        if ($font === null || ! function_exists('imagettftext')) {
            return;
        }

        $glyph = mb_chr((int) hexdec($icon), 'UTF-8');
        $size = 230;
        $box = imagettfbbox($size, 0, $font, $glyph);

        if ($box === false) {
            return;
        }

        $width = $box[2] - $box[0];
        $height = $box[1] - $box[7];
        $x = (int) round(self::WIDTH * 0.68 - $width / 2 - $box[0]);
        $y = (int) round(self::HEIGHT / 2 + $height / 2 - $box[1]);

        imagettftext($image, $size, 0, $x, $y, imagecolorallocatealpha($image, 255, 255, 255, 20), $font, $glyph);
    }

    /**
     * The Font Awesome solid font from node_modules, or the copy Vite places in public/build.
     */
    private static function iconFont(): ?string
    {
        $candidates = [
            base_path('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.ttf'),
            ...(glob(public_path('build/assets/fa-solid-900-*.ttf')) ?: []),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return array{int, int, int}
     */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [(int) hexdec(substr($hex, 0, 2)), (int) hexdec(substr($hex, 2, 2)), (int) hexdec(substr($hex, 4, 2))];
    }
}
