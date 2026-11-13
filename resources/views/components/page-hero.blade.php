@props([
    'eyebrow' => null,
    'title',
    'intro' => null,
    'introLines' => \App\Support\PageContent::INTRO_LINES,
    'titleId' => null,
    'narrow' => false,
    'image' => null,
    'imageAlt' => '',
    // right, left or background. Anything else (or no image) gives a text-only header.
    'imagePosition' => 'right',
])
@php
    $imagePosition = in_array($imagePosition, ['right', 'left', 'background'], true) ? $imagePosition : null;
    $background = $image && $imagePosition === 'background';
    $side = $image && in_array($imagePosition, ['right', 'left'], true);
@endphp
<section {{ $attributes->class(['page-hero', 'relative isolate overflow-hidden', 'hero-on-image bg-secondary text-white' => $background]) }}>
    @if ($background)
        <img src="{{ $image }}" alt="{{ $imageAlt }}" class="absolute inset-0 -z-10 h-full w-full object-cover" fetchpriority="high">
        {{-- Darkens the photo so the text keeps a readable contrast whatever the image. --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-secondary/95 via-secondary/85 to-secondary/60" aria-hidden="true"></div>
    @endif
    <div @class(['container-site section', 'grid gap-layout lg:grid-cols-12 lg:items-center' => $side, 'py-16 sm:py-24 lg:py-28' => $background])>
        <div @class([
            'lg:col-span-7' => $side,
            'lg:order-last' => $side && $imagePosition === 'left',
            'max-w-3xl' => ! $narrow && ! $side,
            'mx-auto max-w-3xl' => $narrow && ! $side,
        ])>
            {{ $before ?? '' }}
            @if ($eyebrow)
                <p class="eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 @if ($titleId) id="{{ $titleId }}" @endif class="mt-3 text-[clamp(2rem,5vw,3.5rem)] leading-[1.1] font-semibold tracking-tight text-balance">{{ $title }}</h1>
            @if ($intro)
                <x-read-more :lines="$introLines" class="mt-4 max-w-2xl" text-class="text-base leading-7 text-muted sm:text-lg sm:leading-8 text-pretty">{{ $intro }}</x-read-more>
            @endif
            {{ $slot }}
        </div>
        @if ($side)
            <div class="lg:col-span-5">
                <img src="{{ $image }}" alt="{{ $imageAlt }}" class="aspect-[4/3] w-full rounded-2xl border border-line object-cover" fetchpriority="high">
            </div>
        @endif
    </div>
</section>
