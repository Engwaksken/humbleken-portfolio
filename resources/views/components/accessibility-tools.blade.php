@php
    // Each toggle maps to a preference key handled in resources/js/accessibility.js.
    $icons = [
        'line-height' => 'M8 6h12M8 12h12M8 18h12M4 4v16M2.5 6 4 4l1.5 2M2.5 18 4 20l1.5-2',
        'spacing' => 'M3 7h18M3 17h18M7 12h10M4 12l2-2v4zM20 12l-2-2v4z',
        'font' => 'M4 20 10 4h1l6 16M6.5 14h8M17 20h4',
        'align' => 'M4 6h16M4 10h10M4 14h16M4 18h10',
        'link' => 'M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1',
        'underline' => 'M7 4v7a5 5 0 0 0 10 0V4M5 20h14',
        'heading' => 'M6 4v16M18 4v16M6 12h12',
        'contrast' => 'M12 3a9 9 0 1 0 0 18zM12 3a9 9 0 0 1 0 18',
        'moon' => 'M20 14.5A8 8 0 1 1 9.5 4 6.5 6.5 0 0 0 20 14.5Z',
        'invert' => 'M12 3v18M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z',
        'grayscale' => 'M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z',
        'saturation' => 'M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11ZM9 15a3 3 0 0 0 3 3',
        'cursor' => 'M5 3l14 9-6 1.5 3.5 6.5-2.5 1.5-3.5-6.5L5 19z',
        'guide' => 'M3 12h18M3 9h18M3 15h18',
        'mask' => 'M3 5h18v4H3zM3 15h18v4H3z',
        'focus' => 'M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3M9 12h6',
        'image' => 'M4 5h16v14H4zM4 15l4-4 5 5M14 13l2-2 4 4M3 3l18 18',
        'motion' => 'M9 6v12M15 6v12',
        'speak' => 'M4 9v6h4l5 4V5L8 9zM16.5 8.5a5 5 0 0 1 0 7M19 6a8.5 8.5 0 0 1 0 12',
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
        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-8 fill-none stroke-current" stroke-width="1.8"><circle cx="12" cy="12" r="9.25"/><circle cx="12" cy="7.25" r="1.4" class="fill-current"/><path d="M7.5 10.25 12 11.5l4.5-1.25M12 11.5v3m0 0-2.25 3.75M12 14.5l2.25 3.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <div class="fixed inset-0 z-[64] bg-ink/40 backdrop-blur-[2px] sm:hidden" hidden data-accessibility-backdrop></div>

    <section id="accessibility-panel" role="dialog" aria-modal="false" aria-labelledby="accessibility-title" hidden
        class="fixed inset-x-0 bottom-0 z-[65] flex max-h-[88dvh] flex-col overflow-hidden rounded-t-3xl border border-line bg-canvas text-ink shadow-2xl animate-fade-up sm:inset-x-auto sm:bottom-24 sm:left-6 sm:max-h-[min(44rem,calc(100dvh-8rem))] sm:w-[26rem] sm:rounded-3xl">
        <header class="flex items-center justify-between gap-3 bg-ink px-5 py-4 text-white">
            <div class="flex items-center gap-3">
                <span aria-hidden="true" class="grid size-10 place-items-center rounded-xl bg-white/15">
                    <svg viewBox="0 0 24 24" class="size-6 fill-none stroke-current" stroke-width="1.8"><circle cx="12" cy="12" r="9.25"/><circle cx="12" cy="7.25" r="1.4" class="fill-current"/><path d="M7.5 10.25 12 11.5l4.5-1.25M12 11.5v3m0 0-2.25 3.75M12 14.5l2.25 3.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <h2 id="accessibility-title" class="font-semibold">Accessibility tools</h2>
                    <p class="text-xs text-white/70">Adjust the site to suit your needs. Alt + A</p>
                </div>
            </div>
            <button type="button" class="grid size-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close accessibility tools" data-accessibility-close>
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
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
                    <button type="button" class="grid size-11 place-items-center rounded-lg bg-brand-tint text-lg font-bold text-brand-dark hover:bg-brand-tint-strong" data-accessibility-action="text-smaller" aria-label="Decrease text size">A−</button>
                    <div class="flex-1 text-center">
                        <p class="text-sm font-semibold" data-accessibility-text-size>100%</p>
                        <div class="mx-auto mt-1.5 h-1.5 w-full max-w-[10rem] overflow-hidden rounded-full bg-line" aria-hidden="true"><div class="h-full rounded-full bg-brand transition-all" style="width: 33%" data-accessibility-text-bar></div></div>
                    </div>
                    <button type="button" class="grid size-11 place-items-center rounded-lg bg-brand-tint text-lg font-bold text-brand-dark hover:bg-brand-tint-strong" data-accessibility-action="text-larger" aria-label="Increase text size">A+</button>
                </div>
            </section>

            @foreach ($sections as $heading => $options)
                <section aria-labelledby="a11y-{{ \Illuminate\Support\Str::slug($heading) }}">
                    <h3 id="a11y-{{ \Illuminate\Support\Str::slug($heading) }}" class="text-xs font-bold tracking-[.14em] text-muted uppercase">{{ $heading }}</h3>
                    <div class="mt-3 grid grid-cols-2 gap-2 min-[400px]:grid-cols-3">
                        @foreach ($options as [$key, $label, $icon])
                            <button type="button" class="{{ $option }}" data-accessibility-toggle-option="{{ $key }}" aria-pressed="false" @if ($key === 'readAloud') data-requires-speech @endif>
                                <span class="{{ $iconWrap }}" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-[1.125rem] fill-none stroke-current" stroke-width="1.9"><path d="{{ $icons[$icon] }}" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
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
                        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4 fill-current"><path d="M7 5v14l12-7z"/></svg>Read this page
                    </button>
                    <button type="button" class="btn-ghost !rounded-xl" data-accessibility-action="speak-stop">
                        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4 fill-current"><path d="M6 6h12v12H6z"/></svg>Stop reading
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
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4 fill-none stroke-current" stroke-width="2"><path d="M4 12a8 8 0 1 0 2.34-5.66M4 4v5h5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Reset all settings
            </button>
        </footer>
        <p class="sr-only" data-accessibility-status role="status" aria-live="polite"></p>
    </section>
</div>
