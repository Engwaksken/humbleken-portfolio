<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Admin-editable copy and images for the home page and the fixed site pages.
 *
 * Each page is stored as one JSON setting ("page_content_{key}"). Values left blank fall
 * back to the defaults below, and the older one-field-per-setting keys (home_title,
 * services_intro, approach_steps, ...) are still honoured for sites that set them before
 * the page editor existed.
 */
class PageContent
{
    public const PAGES = ['home', 'services', 'approach', 'booking', 'contact'];

    /** Directory on the public disk that holds page images. */
    public const MEDIA_DIRECTORY = 'page-media';

    /** Font Awesome icons offered for the "Why work with us" items (the mobile app maps the same names). */
    public const ICONS = [
        'fa-comments' => 'Conversation',
        'fa-scale-balanced' => 'Balance',
        'fa-circle-check' => 'Check mark',
        'fa-shield-halved' => 'Shield',
        'fa-bolt' => 'Lightning bolt',
        'fa-gears' => 'Gears',
        'fa-server' => 'Server',
        'fa-network-wired' => 'Network',
        'fa-laptop-code' => 'Laptop',
        'fa-cloud' => 'Cloud',
        'fa-lock' => 'Lock',
        'fa-headset' => 'Headset',
        'fa-chart-line' => 'Chart',
        'fa-lightbulb' => 'Light bulb',
        'fa-clock' => 'Clock',
        'fa-users' => 'People',
    ];

    public const HERO_ASIDES = [
        'card' => 'Getting-started card',
        'image' => 'Image',
        'none' => 'Nothing (text only)',
    ];

    /** Default number of introduction lines shown before "Read more". */
    public const INTRO_LINES = 2;

    /** Where the hero image sits when the hero shows an image. */
    public const HERO_IMAGE_POSITIONS = [
        'right' => 'Right of the text',
        'left' => 'Left of the text',
        'background' => 'Behind the text (background)',
    ];

    /**
     * Default content for each page, in the flat shape used by the admin form.
     *
     * @return array<string, mixed>
     */
    public static function defaults(string $page): array
    {
        $common = ['intro_lines' => static::INTRO_LINES, 'image' => null, 'image_alt' => '', 'body' => '', 'gallery' => []];

        return match ($page) {
            'home' => [
                'hero_eyebrow' => '',
                'hero_title' => 'Engineering solutions for reliable, effective IT.',
                'hero_intro' => 'Practical IT consulting that finds the cause of recurring problems and fixes it. We assess your systems, explain the options in plain language and help you choose a sensible way forward. From a single troublesome server to a wider infrastructure review, you get clear recommendations and hands-on engineering support through to a working result.',
                'hero_intro_lines' => static::INTRO_LINES,
                'hero_primary_label' => 'Explore services',
                'hero_secondary_label' => 'Book a consultation',
                'hero_aside' => 'card',
                'hero_image' => null,
                'hero_image_alt' => '',
                'hero_image_position' => 'right',
                'card_eyebrow' => 'Start with the challenge',
                'card_text' => 'Describe the issue or objective. We can clarify the requirements and identify a suitable path forward.',
                'card_steps' => [
                    ['text' => 'Share the problem or goal'],
                    ['text' => 'Get a clear, jargon-free assessment'],
                    ['text' => 'Move forward with a practical plan'],
                ],
                'card_link_label' => 'Send an enquiry',
                'services_enabled' => true,
                'services_eyebrow' => 'What we do',
                'services_heading' => 'Services',
                'services_link_label' => 'All services',
                'services_count' => 3,
                'why_enabled' => true,
                'why_eyebrow' => 'Why work with us',
                'why_heading' => 'Engineering that is clear, careful and practical.',
                'why_items' => [
                    ['icon' => 'fa-comments', 'heading' => 'Clear communication', 'text' => 'Technical findings explained in plain language, so you can make confident decisions.', 'image' => null],
                    ['icon' => 'fa-scale-balanced', 'heading' => 'Proportionate solutions', 'text' => 'Recommendations sized to the problem and your budget, with no unnecessary complexity.', 'image' => null],
                    ['icon' => 'fa-circle-check', 'heading' => 'Reliable follow-through', 'text' => 'Practical engineering support from diagnosis through to a working result.', 'image' => null],
                ],
                'projects_enabled' => true,
                'projects_eyebrow' => 'Portfolio',
                'projects_heading' => 'Recent work',
                'projects_link_label' => 'All projects',
                'projects_count' => 3,
                'cta_enabled' => true,
                'cta_title' => 'Have a technical challenge in mind?',
                'cta_text' => 'Tell us about the issue or objective and we will help you find a practical way forward.',
            ],
            'services' => $common + [
                'eyebrow' => 'Services',
                'title' => 'Technical expertise for systems and decisions.',
                'intro' => 'Focused consulting and engineering support to address operational issues, strengthen systems, and make informed technology choices.',
            ],
            'approach' => $common + [
                'eyebrow' => 'Approach',
                'title' => 'Clear analysis. Practical recommendations.',
                'intro' => 'A straightforward process to understand the challenge, evaluate options, and move forward with confidence.',
                'steps' => static::stepsFromLines("Understand the current environment, the issue, and the outcome you need.\nAssess options and explain technical implications and trade-offs in clear language.\nDevelop a proportionate path forward focused on effective engineering and reliable systems."),
            ],
            'booking' => $common + [
                'eyebrow' => 'Appointment request',
                'title' => 'Request a consultation.',
                'intro' => 'Share a preferred time and we’ll get back to you to confirm availability. This is a request, not a confirmed booking.',
                'steps' => static::stepsFromLines("We review your request and preferred time.\nWe contact you to confirm availability.\nWe meet to discuss your requirements."),
            ],
            'contact' => $common + [
                'eyebrow' => 'Contact',
                'title' => 'Discuss your technical requirements.',
                'intro' => 'Share the issue, objective, or decision you are working through to begin a focused discussion.',
            ],
            default => throw new \InvalidArgumentException("Unknown page [{$page}]."),
        };
    }

    /**
     * Stored content merged over the defaults, in the flat admin-form shape.
     *
     * @return array<string, mixed>
     */
    public static function raw(string $page): array
    {
        $content = static::defaults($page);

        foreach (static::legacyValues($page) as $key => $value) {
            if (filled($value)) {
                $content[$key] = $value;
            }
        }

        $stored = Settings::get(static::settingKey($page));
        $stored = is_string($stored) ? json_decode($stored, true) : $stored;

        foreach (is_array($stored) ? $stored : [] as $key => $value) {
            if (! array_key_exists($key, $content)) {
                continue;
            }

            // Blank text falls back to the default; lists, toggles and images are taken as saved.
            if (is_string($content[$key]) && blank($value)) {
                continue;
            }

            $content[$key] = is_array($value) ? array_values($value) : $value;
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function save(string $page, array $data): void
    {
        $defaults = static::defaults($page);
        $data = array_intersect_key($data, $defaults);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = array_values($value);
            }
        }

        Settings::set(static::settingKey($page), $data);
    }

    /**
     * Content ready for the website and API: nested sections with image URLs.
     *
     * @return array<string, mixed>
     */
    public static function get(string $page): array
    {
        $raw = static::raw($page);

        if ($page === 'home') {
            return static::home($raw);
        }

        $content = [
            'key' => $page,
            'eyebrow' => $raw['eyebrow'],
            'title' => $raw['title'],
            'intro' => $raw['intro'],
            'intro_lines' => static::introLines($raw['intro_lines']),
            'image_url' => static::mediaUrl($raw['image']),
            'image_alt' => (string) $raw['image_alt'],
            'body' => (string) $raw['body'],
            'steps' => [],
            'gallery' => collect($raw['gallery'])
                ->map(fn (mixed $item): array => [
                    'image_url' => static::mediaUrl(is_array($item) ? ($item['image'] ?? null) : null),
                    'caption' => is_array($item) ? (string) ($item['caption'] ?? '') : '',
                ])
                ->filter(fn (array $item): bool => $item['image_url'] !== null)
                ->values()
                ->all(),
        ];

        if (array_key_exists('steps', $raw)) {
            $content['steps'] = collect($raw['steps'])
                ->filter(fn (mixed $step): bool => is_array($step) && filled($step['text'] ?? null))
                ->map(fn (array $step): array => [
                    'title' => filled($step['title'] ?? null) ? (string) $step['title'] : null,
                    'text' => (string) $step['text'],
                    'image_url' => static::mediaUrl($step['image'] ?? null),
                ])
                ->values()
                ->all();
        }

        return $content;
    }

    /**
     * Public URL of a stored page image, or null when unset or missing.
     */
    public static function mediaUrl(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = collect($path)->first(fn (mixed $value): bool => is_string($value));
        }

        if (! is_string($path) || ! static::isMediaPath($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return route('page-media.show', ['file' => basename($path)]);
    }

    public static function isMediaPath(string $path): bool
    {
        return (bool) preg_match('#^'.static::MEDIA_DIRECTORY.'/[A-Za-z0-9_-]+\.(webp|png|jpe?g|gif)$#', $path);
    }

    /**
     * Every page image currently referenced by saved page content.
     *
     * @return list<string>
     */
    public static function referencedMedia(): array
    {
        return collect(static::PAGES)
            ->flatMap(fn (string $page): array => static::mediaPathsIn(static::raw($page)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Delete page images that no saved page refers to any more.
     */
    public static function pruneUnusedMedia(): void
    {
        $disk = Storage::disk('public');
        $referenced = array_flip(static::referencedMedia());

        foreach ($disk->files(static::MEDIA_DIRECTORY) as $path) {
            if (static::isMediaPath($path) && ! isset($referenced[$path])) {
                $disk->delete($path);
            }
        }
    }

    /** Lines of an introduction shown before "Read more", kept to 0 (show all) to 10. */
    public static function introLines(mixed $value): int
    {
        return is_numeric($value) ? max(0, min(10, (int) $value)) : static::INTRO_LINES;
    }

    public static function settingKey(string $page): string
    {
        if (! in_array($page, static::PAGES, true)) {
            throw new \InvalidArgumentException("Unknown page [{$page}].");
        }

        return 'page_content_'.$page;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private static function home(array $raw): array
    {
        $aside = array_key_exists($raw['hero_aside'], static::HERO_ASIDES) ? $raw['hero_aside'] : 'card';
        $heroImage = static::mediaUrl($raw['hero_image']);

        return [
            'hero' => [
                'eyebrow' => filled($raw['hero_eyebrow']) ? $raw['hero_eyebrow'] : (Settings::get('tagline') ?: 'Engineering solutions and technical consulting'),
                'title' => $raw['hero_title'],
                'intro' => $raw['hero_intro'],
                // Lines of the introduction shown before "Read more"; 0 shows it all.
                'intro_lines' => static::introLines($raw['hero_intro_lines']),
                'primary_label' => $raw['hero_primary_label'],
                'secondary_label' => $raw['hero_secondary_label'],
                // An image aside without an image would leave an empty column.
                'aside' => $aside === 'image' && $heroImage === null ? 'none' : $aside,
                'image_url' => $heroImage,
                'image_alt' => (string) $raw['hero_image_alt'],
                'image_position' => array_key_exists($raw['hero_image_position'], static::HERO_IMAGE_POSITIONS) ? $raw['hero_image_position'] : 'right',
                'card' => [
                    'eyebrow' => $raw['card_eyebrow'],
                    'text' => $raw['card_text'],
                    'steps' => collect($raw['card_steps'])
                        ->map(fn (mixed $step): string => trim((string) (is_array($step) ? ($step['text'] ?? '') : $step)))
                        ->filter()
                        ->values()
                        ->all(),
                    'link_label' => $raw['card_link_label'],
                ],
            ],
            'services' => [
                'enabled' => (bool) $raw['services_enabled'],
                'eyebrow' => $raw['services_eyebrow'],
                'heading' => $raw['services_heading'],
                'link_label' => $raw['services_link_label'],
                'count' => max(1, min(12, (int) $raw['services_count'])),
            ],
            'why' => [
                'enabled' => (bool) $raw['why_enabled'],
                'eyebrow' => $raw['why_eyebrow'],
                'heading' => $raw['why_heading'],
                'items' => collect($raw['why_items'])
                    ->filter(fn (mixed $item): bool => is_array($item) && (filled($item['heading'] ?? null) || filled($item['text'] ?? null)))
                    ->map(fn (array $item): array => [
                        'icon' => array_key_exists($item['icon'] ?? '', static::ICONS) ? $item['icon'] : 'fa-circle-check',
                        'heading' => (string) ($item['heading'] ?? ''),
                        'text' => (string) ($item['text'] ?? ''),
                        'image_url' => static::mediaUrl($item['image'] ?? null),
                    ])
                    ->values()
                    ->all(),
            ],
            'projects' => [
                'enabled' => (bool) $raw['projects_enabled'],
                'eyebrow' => $raw['projects_eyebrow'],
                'heading' => $raw['projects_heading'],
                'link_label' => $raw['projects_link_label'],
                'count' => max(1, min(12, (int) $raw['projects_count'])),
            ],
            'cta' => [
                'enabled' => (bool) $raw['cta_enabled'],
                'title' => $raw['cta_title'],
                'text' => $raw['cta_text'],
            ],
        ];
    }

    /**
     * Values saved by the earlier one-setting-per-field page content form.
     *
     * @return array<string, mixed>
     */
    private static function legacyValues(string $page): array
    {
        if ($page === 'home') {
            return [
                'hero_title' => Settings::get('home_title'),
                'hero_intro' => Settings::get('home_intro'),
            ];
        }

        $values = [
            'title' => Settings::get($page.'_title'),
            'intro' => Settings::get($page.'_intro'),
        ];

        if ($page === 'approach' && filled($steps = Settings::get('approach_steps'))) {
            $values['steps'] = static::stepsFromLines((string) $steps);
        }

        return $values;
    }

    /**
     * @return list<array{title: null, text: string, image: null}>
     */
    private static function stepsFromLines(string $lines): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $lines))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(fn (string $line): array => ['title' => null, 'text' => $line, 'image' => null])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $values
     * @return list<string>
     */
    private static function mediaPathsIn(array $values): array
    {
        $paths = [];

        array_walk_recursive($values, function (mixed $value) use (&$paths): void {
            if (is_string($value) && static::isMediaPath($value)) {
                $paths[] = $value;
            }
        });

        return $paths;
    }
}
