@props(['eyebrow' => null, 'title', 'intro' => null, 'titleId' => null, 'narrow' => false, 'image' => null, 'imageAlt' => ''])
<section {{ $attributes->class('page-hero') }}>
    <div @class(['container-site section', 'grid gap-layout lg:grid-cols-12 lg:items-center' => $image])>
        <div @class(['lg:col-span-7' => $image, 'max-w-3xl' => ! $narrow && ! $image, 'mx-auto max-w-3xl' => $narrow && ! $image])>
            {{ $before ?? '' }}
            @if ($eyebrow)
                <p class="eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 @if ($titleId) id="{{ $titleId }}" @endif class="mt-3 text-[clamp(2rem,5vw,3.5rem)] leading-[1.1] font-semibold tracking-tight text-balance">{{ $title }}</h1>
            @if ($intro)
                <p class="mt-4 max-w-2xl text-base leading-7 text-muted sm:text-lg sm:leading-8 text-pretty">{{ $intro }}</p>
            @endif
            {{ $slot }}
        </div>
        @if ($image)
            <div class="lg:col-span-5">
                <img src="{{ $image }}" alt="{{ $imageAlt }}" class="aspect-[4/3] w-full rounded-2xl border border-line object-cover" fetchpriority="high">
            </div>
        @endif
    </div>
</section>
