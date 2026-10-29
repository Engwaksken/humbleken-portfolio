<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Public-site appearance derived from admin Settings: brand colour, font and base size.
 */
class Theme
{
    public const DEFAULT_COLOR = '#077784';

    public const DEFAULT_SECONDARY_COLOR = '#14263d';

    /** Light backgrounds that brand-coloured text and links sit on. */
    private const BACKGROUNDS = ['#ffffff', '#f4f7fb'];

    /**
     * @var array<string, array{label: string, stack: string, webfont: ?string}>
     */
    public const FONTS = [
        'system' => [
            'label' => 'System default (no download)',
            'stack' => "ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'",
            'webfont' => null,
        ],
        'instrument-sans' => [
            'label' => 'Instrument Sans',
            'stack' => "'Instrument Sans', ui-sans-serif, system-ui, sans-serif",
            'webfont' => 'instrument-sans:400,500,600,700',
        ],
        'inter' => [
            'label' => 'Inter',
            'stack' => "'Inter', ui-sans-serif, system-ui, sans-serif",
            'webfont' => 'inter:400,500,600,700',
        ],
        'source-serif-4' => [
            'label' => 'Source Serif 4',
            'stack' => "'Source Serif 4', Georgia, serif",
            'webfont' => 'source-serif-4:400,600,700',
        ],
        'georgia' => [
            'label' => 'Georgia (serif, no download)',
            'stack' => "Georgia, 'Times New Roman', serif",
            'webfont' => null,
        ],
    ];

    /**
     * Root font size; Tailwind sizes are in rem, so this scales all text and spacing.
     *
     * @var array<string, array{label: string, value: string}>
     */
    public const SIZES = [
        'small' => ['label' => 'Small', 'value' => '93.75%'],
        'medium' => ['label' => 'Medium (default)', 'value' => '100%'],
        'large' => ['label' => 'Large', 'value' => '112.5%'],
    ];

    public static function color(): string
    {
        $color = strtolower((string) Settings::get('theme_color'));

        return static::isHex($color) && static::problem($color) === null ? $color : self::DEFAULT_COLOR;
    }

    public static function secondaryColor(): string
    {
        $color = strtolower((string) Settings::get('theme_secondary_color'));

        return static::isHex($color) && static::problem($color) === null ? $color : self::DEFAULT_SECONDARY_COLOR;
    }

    public static function fontStack(): string
    {
        $custom = trim((string) Settings::get('theme_custom_font'));

        if ($custom !== '' && preg_match('/^[\pL\pN][\pL\pN \-_]{0,79}$/u', $custom)) {
            return "'{$custom}', ui-sans-serif, system-ui, sans-serif";
        }

        return self::FONTS[static::fontKey()]['stack'];
    }

    public static function fontKey(): string
    {
        $key = (string) Settings::get('theme_font');

        return array_key_exists($key, self::FONTS) ? $key : 'system';
    }

    public static function sizeKey(): string
    {
        $key = (string) Settings::get('theme_font_size');

        return array_key_exists($key, self::SIZES) ? $key : 'medium';
    }

    /**
     * The <style> and font <link> to place in the public layout's <head>.
     */
    public static function headTags(): HtmlString
    {
        $color = static::color();
        $secondaryColor = static::secondaryColor();

        $variables = [
            '--font-sans' => static::fontStack(),
        ];

        // The default palette is hand-tuned in app.css; only derive shades for a custom colour.
        if ($color !== self::DEFAULT_COLOR) {
            $variables += [
                '--color-brand' => $color,
                '--color-brand-dark' => static::mix($color, '#000000', 0.22),
                '--color-brand-tint' => static::mix($color, '#ffffff', 0.9),
                '--color-brand-tint-strong' => static::mix($color, '#ffffff', 0.8),
            ];
        }

        if ($secondaryColor !== self::DEFAULT_SECONDARY_COLOR) {
            $variables += [
                '--color-secondary' => $secondaryColor,
                '--color-secondary-dark' => static::mix($secondaryColor, '#000000', 0.18),
                '--color-secondary-tint' => static::mix($secondaryColor, '#ffffff', 0.92),
            ];
        }

        $css = collect($variables)->map(fn (string $value, string $name): string => "{$name}: {$value};")->implode(' ');
        $html = '<style>:root { '.$css.' } html { font-size: '.self::SIZES[static::sizeKey()]['value'].'; }</style>';

        $font = self::FONTS[static::fontKey()];
        if ($font['webfont'] !== null && trim((string) Settings::get('theme_custom_font')) === '') {
            $html = '<link rel="preconnect" href="https://fonts.bunny.net">'
                .'<link rel="stylesheet" href="https://fonts.bunny.net/css?family='.e($font['webfont']).'&display=swap">'
                .$html;
        }

        return new HtmlString($html);
    }

    /**
     * Why a colour cannot be used, or null when it is readable everywhere it appears.
     */
    public static function problem(string $color): ?string
    {
        if (! static::isHex($color)) {
            return 'Use a six-digit hex colour such as #077784.';
        }

        $checks = [];
        foreach (self::BACKGROUNDS as $background) {
            $checks["on {$background}"] = static::contrast($color, $background);
        }
        $checks['on its light tint'] = static::contrast($color, static::mix($color, '#ffffff', 0.9));
        $checks['behind white button text'] = static::contrast('#ffffff', $color);

        $worst = collect($checks)->sort()->keys()->first();
        $ratio = $checks[$worst];

        return $ratio < 4.5
            ? sprintf('This colour is too light to read %s (contrast %.2f:1; at least 4.5:1 is needed). Choose a darker shade.', $worst, $ratio)
            : null;
    }

    public static function isHex(string $color): bool
    {
        return (bool) preg_match('/^#[0-9a-f]{6}$/i', $color);
    }

    /**
     * WCAG 2.x contrast ratio between two hex colours.
     */
    public static function contrast(string $a, string $b): float
    {
        [$lighter, $darker] = collect([static::luminance($a), static::luminance($b)])->sortDesc()->values()->all();

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Mix $color towards $with by $amount (0 = $color, 1 = $with).
     */
    public static function mix(string $color, string $with, float $amount): string
    {
        $from = static::rgb($color);
        $to = static::rgb($with);

        return '#'.collect($from)
            ->map(fn (int $channel, int $i): string => str_pad(dechex((int) round($channel + ($to[$i] - $channel) * $amount)), 2, '0', STR_PAD_LEFT))
            ->implode('');
    }

    private static function luminance(string $color): float
    {
        $channels = array_map(function (int $channel): float {
            $c = $channel / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, static::rgb($color));

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * @return array{int, int, int}
     */
    private static function rgb(string $color): array
    {
        $hex = ltrim($color, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
