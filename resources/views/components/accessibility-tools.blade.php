@php
    // Each toggle maps to a preference key handled in resources/js/accessibility.js.
    $icons = [
        'line-height' => 'fa-text-height',
        'spacing' => 'fa-text-width',
        'font' => 'fa-font',
        'align' => 'fa-align-left',
        'link' => 'fa-link',
        'underline' => 'fa-underline',
        'heading' => 'fa-heading',
        'contrast' => 'fa-circle-half-stroke',
        'moon' => 'fa-moon',
        'invert' => 'fa-droplet-slash',
        'grayscale' => 'fa-droplet',
        'saturation' => 'fa-palette',
        'cursor' => 'fa-arrow-pointer',
        'guide' => 'fa-ruler-horizontal',
        'mask' => 'fa-window-maximize',
        'focus' => 'fa-expand',
        'image' => 'fa-image',
        'motion' => 'fa-pause',
        'speak' => 'fa-volume-high',
    ];
    $sections = [
        'Content' => [
            ['lineHeight', 'Line height', 'line-height'],
            ['letterSpacing', 'Text spacing', 'spacing'],
            ['readableFont', 'Readable font', 'font'],
            ['alignLeft', 'Align text left', 'align'],
            ['underlinedLinks', 'Underline links', 'underline'],
            ['highlightLinks', 'Highlight links', 'link'],
            ['highlightHeadings', 'Highlight headings', 'heading'],
        ],
        'Colour & contrast' => [
            ['highContrast', 'High contrast', 'contrast'],
            ['darkContrast', 'Dark contrast', 'moon'],
            ['invert', 'Invert colours', 'invert'],
            ['grayscale', 'Monochrome', 'grayscale'],
            ['lowSaturation', 'Low saturation', 'saturation'],
        ],
        'Navigation & focus' => [
            ['bigCursor', 'Big cursor', 'cursor'],
            ['readingGuide', 'Reading guide', 'guide'],
            ['readingMask', 'Reading mask', 'mask'],
            ['strongFocus', 'Focus highlight', 'focus'],
            ['hideImages', 'Hide images', 'image'],
            ['reducedMotion', 'Stop animations', 'motion'],
            ['readAloud', 'Read on click', 'speak'],
        ],
    ];
    $profiles = [
        ['lowVision', 'Low vision', 'Larger text, high contrast, focus highlight'],
        ['dyslexia', 'Dyslexia', 'Readable font and wider spacing'],
        ['adhd', 'Focus / ADHD', 'Reading mask, no animations'],
        ['seizure', 'Seizure safe', 'Stops motion, lowers saturation'],
        ['motor', 'Motor / keyboard', 'Big cursor, strong focus outline'],
        ['blind', 'Screen reader', 'Read on click, underlined links'],
    ];
    $option = 'a11y-option group flex min-h-[4.25rem] flex-col items-start justify-between gap-2 rounded-xl border border-line bg-white p-3 text-left text-[0.8125rem] font-medium leading-tight text-ink transition hover:border-brand/50';
    $iconWrap = 'a11y-icon grid size-8 place-items-center rounded-lg bg-brand-tint text-brand-dark';
@endphp
<div data-a11y-exempt data-accessibility-tools>
    <button type="button" aria-expanded="false" aria-controls="accessibility-panel" aria-haspopup="dialog" aria-label="Open accessibility tools (Alt + A)" title="Accessibility tools (Alt + A)" class="fixed bottom-4 left-4 z-[60] grid size-14 place-items-center rounded-full bg-ink text-white shadow-[0_10px_30px_-8px_rgb(20_38_61/0.6)] ring-4 ring-white transition hover:scale-105 hover:bg-secondary-dark sm:bottom-6 sm:left-6" data-accessibility-toggle>
        <i class="fa-solid fa-universal-access text-3xl" aria-hidden="true"></i>
    </button>

    <div class="fixed inset-0 z-[64] bg-ink/40 backdrop-blur-[2px] sm:hidden" hidden data-accessibility-backdrop></div>

    <section id="accessibility-panel" role="dialog" aria-modal="false" aria-labelledby="accessibility-title" hidden
        class="fixed inset-x-0 bottom-0 z-[65] flex max-h-[88dvh] flex-col overflow-hidden rounded-t-3xl border border-line bg-canvas text-ink shadow-2xl animate-fade-up sm:inset-x-auto sm:bottom-24 sm:left-6 sm:max-h-[min(44rem,calc(100dvh-8rem))] sm:w-[26rem] sm:rounded-3xl">
        <header class="flex items-center justify-between gap-3 bg-ink px-5 py-4 text-white">
            <div class="flex items-center gap-3">
                <span aria-hidden="true" class="grid size-10 place-items-center rounded-xl bg-white/15">
                    <i class="fa-solid fa-universal-access text-xl"></i>
                </span>
                <div>
                    <h2 id="accessibility-title" class="font-semibold">Accessibility tools</h2>
                    <p class="text-xs text-white/70">Adjust the site to suit your needs. Alt + A</p>
                </div>
            </div>
            <button type="button" class="grid size-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close accessibility tools" data-accessibility-close>
                <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
            </button>
        </header>

        <div class="flex-1 space-y-6 overflow-y-auto px-4 py-5 chat-scroll sm:px-5">
            <section aria-labelledby="a11y-profiles">
                <h3 id="a11y-profiles" class="text-xs font-bold tracking-[.14em] text-muted uppercase">Quick profiles</h3>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach ($profiles as [$key, $label, $hint])
                        <button type="button" class="{{ $option }} !min-h-0" data-accessibility-profile="{{ $key }}" aria-pressed="false">
                            <span class="font-semibold">{{ $label }}</span>
                            <span class="text-xs font-normal text-muted group-aria-pressed:text-white/85">{{ $hint }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="a11y-text-size">
                <h3 id="a11y-text-size" class="text-xs font-bold tracking-[.14em] text-muted uppercase">Text size</h3>
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-line bg-white p-2">
                    <button type="button" class="grid size-11 place-items-center rounded-lg bg-brand-tint text-lg font-bold text-brand-dark hover:bg-brand-tint-strong" data-accessibility-action="text-smaller" aria-label="Decrease text size"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                    <div class="flex-1 text-center">
                        <p class="text-sm font-semibold" data-accessibility-text-size>100%</p>
                        <div class="mx-auto mt-1.5 h-1.5 w-full max-w-[10rem] overflow-hidden rounded-full bg-line" aria-hidden="true"><div class="h-full rounded-full bg-brand transition-all" style="width: 33%" data-accessibility-text-bar></div></div>
                    </div>
                    <button type="button" class="grid size-11 place-items-center rounded-lg bg-brand-tint text-lg font-bold text-brand-dark hover:bg-brand-tint-strong" data-accessibility-action="text-larger" aria-label="Increase text size"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                </div>
            </section>

            @foreach ($sections as $heading => $options)
                <section aria-labelledby="a11y-{{ \Illuminate\Support\Str::slug($heading) }}">
                    <h3 id="a11y-{{ \Illuminate\Support\Str::slug($heading) }}" class="text-xs font-bold tracking-[.14em] text-muted uppercase">{{ $heading }}</h3>
                    <div class="mt-3 grid grid-cols-2 gap-2 min-[400px]:grid-cols-3">
                        @foreach ($options as [$key, $label, $icon])
                            <button type="button" class="{{ $option }}" data-accessibility-toggle-option="{{ $key }}" aria-pressed="false" @if ($key === 'readAloud') data-requires-speech @endif>
                                <span class="{{ $iconWrap }}" aria-hidden="true"><i class="fa-solid {{ $icons[$icon] }}"></i></span>
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section aria-labelledby="a11y-read" data-requires-speech>
                <h3 id="a11y-read" class="text-xs font-bold tracking-[.14em] text-muted uppercase">Read aloud</h3>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" class="btn-ghost !rounded-xl" data-accessibility-action="speak-page">
                        <i class="fa-solid fa-play" aria-hidden="true"></i>Read this page
                    </button>
                    <button type="button" class="btn-ghost !rounded-xl" data-accessibility-action="speak-stop">
                        <i class="fa-solid fa-stop" aria-hidden="true"></i>Stop reading
                    </button>
                </div>
            </section>

            <details class="rounded-xl border border-line bg-white p-4 text-sm">
                <summary class="cursor-pointer font-semibold">Keyboard shortcuts</summary>
                <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-muted">
                    <dt><kbd class="rounded border border-line bg-canvas px-1.5 font-mono text-xs">Alt</kbd> + <kbd class="rounded border border-line bg-canvas px-1.5 font-mono text-xs">A</kbd></dt><dd>Open or close these tools</dd>
                    <dt><kbd class="rounded border border-line bg-canvas px-1.5 font-mono text-xs">Alt</kbd> + <kbd class="rounded border border-line bg-canvas px-1.5 font-mono text-xs">C</kbd></dt><dd>Open or close the chat assistant</dd>
                    <dt><kbd class="rounded border border-line bg-canvas px-1.5 font-mono text-xs">Tab</kbd></dt><dd>Move to the next link or control</dd>
                    <dt><kbd class="rounded border border-line bg-canvas px-1.5 font-mono text-xs">Esc</kbd></dt><dd>Close an open panel or menu</dd>
                </dl>
            </details>
        </div>

        <footer class="border-t border-line bg-white p-4">
            <button type="button" data-accessibility-action="reset" class="btn-dark w-full !rounded-xl">
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                Reset all settings
            </button>
        </footer>
        <p class="sr-only" data-accessibility-status role="status" aria-live="polite"></p>
    </section>
</div>
