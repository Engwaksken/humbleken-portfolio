@props(['eyebrow' => null, 'title', 'intro' => null, 'titleId' => null, 'narrow' => false])
<section {{ $attributes->class('page-hero') }}>
    <div class="container-site py-14 sm:py-20 lg:py-24">
        <div @class(['max-w-3xl' => ! $narrow, 'mx-auto max-w-3xl' => $narrow])>
            {{ $before ?? '' }}
            @if ($eyebrow)
                <p class="eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 @if ($titleId) id="{{ $titleId }}" @endif class="mt-4 text-[clamp(2rem,5vw,3.5rem)] leading-[1.1] font-semibold tracking-tight text-balance">{{ $title }}</h1>
            @if ($intro)
                <p class="mt-5 max-w-2xl text-lg leading-8 text-muted text-pretty">{{ $intro }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</section>
