@php
    ['hero' => $hero, 'services' => $servicesSection, 'why' => $why, 'projects' => $projectsSection, 'cta' => $cta] = $page;
    $showProjects = $projectsSection['enabled'] && $featuredProjects->isNotEmpty();
@endphp
<x-layouts.site :title="$brandName.' — '.$tagline" :description="$hero['intro']">
    {{-- Hero --}}
    <section class="border-b border-line bg-white">
        <div class="container-site section grid gap-layout lg:grid-cols-12 lg:items-center">
            <div @class(['lg:col-span-7' => $hero['aside'] !== 'none', 'lg:col-span-9' => $hero['aside'] === 'none'])>
                @if (filled($hero['eyebrow']))
                    <p class="eyebrow">{{ $hero['eyebrow'] }}</p>
                @endif
                <h1 class="mt-4 text-[clamp(2rem,6vw,4.25rem)] leading-[1.05] font-semibold tracking-tight text-balance">{{ $hero['title'] }}</h1>
                @if (filled($hero['intro']))
                    <p class="mt-4 max-w-2xl text-base leading-7 text-muted sm:text-lg sm:leading-8 text-pretty">{{ $hero['intro'] }}</p>
                @endif
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('services.index') }}" class="btn-primary">{{ $hero['primary_label'] }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    <a href="{{ route('booking') }}" class="btn-ghost">{{ $hero['secondary_label'] }}</a>
                </div>
            </div>
            @if ($hero['aside'] === 'image')
                <div class="lg:col-span-5">
                    <img src="{{ $hero['image_url'] }}" alt="{{ $hero['image_alt'] }}" class="aspect-[4/3] w-full rounded-2xl border border-line object-cover" fetchpriority="high">
                </div>
            @elseif ($hero['aside'] === 'card')
                <aside class="lg:col-span-5" aria-label="How to get started">
                    <div class="card p-5 sm:p-8">
                        @if (filled($hero['card']['eyebrow']))
                            <p class="eyebrow">{{ $hero['card']['eyebrow'] }}</p>
                        @endif
                        @if (filled($hero['card']['text']))
                            <p class="mt-3 text-lg leading-snug font-medium sm:text-2xl">{{ $hero['card']['text'] }}</p>
                        @endif
                        @if (! empty($hero['card']['steps']))
                            <ol class="mt-5 space-y-3 border-t border-line pt-5 text-sm">
                                @foreach ($hero['card']['steps'] as $i => $step)
                                    <li class="flex items-center gap-3">
                                        <span class="w-5 shrink-0 text-sm font-bold text-brand">{{ $i + 1 }}.</span>
                                        <span class="font-medium">{{ $step }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                        @if (filled($hero['card']['link_label']))
                            <a href="{{ route('contact') }}" class="link-arrow mt-5">{{ $hero['card']['link_label'] }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                        @endif
                    </div>
                </aside>
            @endif
        </div>
    </section>

    {{-- Services --}}
    @if ($servicesSection['enabled'] && $featuredServices->isNotEmpty())
        <section aria-labelledby="home-services" class="section">
            <div class="container-site">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        @if (filled($servicesSection['eyebrow']))
                            <p class="eyebrow">{{ $servicesSection['eyebrow'] }}</p>
                        @endif
                        <h2 id="home-services" class="mt-3 text-2xl font-semibold tracking-tight sm:text-4xl">{{ $servicesSection['heading'] }}</h2>
                    </div>
                    @if (filled($servicesSection['link_label']))
                        <a href="{{ route('services.index') }}" class="link-arrow">{{ $servicesSection['link_label'] }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    @endif
                </div>
                <div class="section-body grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredServices as $service)
                        <a href="{{ route('services.show', $service->slug) }}" class="card-hover group flex flex-col p-5 sm:p-7">
                            <span class="text-sm font-bold tracking-widest text-brand" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-3 text-xl font-semibold">{{ $service->name }}</h3>
                            <p class="mt-3 line-clamp-4 leading-7 text-muted">{{ $service->summary ?: $service->description }}</p>
                            <span class="link-arrow mt-auto pt-6">Learn more <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Why us --}}
    @if ($why['enabled'])
        <section aria-labelledby="home-why" class="section bg-secondary text-white">
            <div class="container-site grid gap-layout lg:grid-cols-12">
                <div class="lg:col-span-4">
                    @if (filled($why['eyebrow']))
                        <p class="text-xs font-bold tracking-[.18em] text-white/70 uppercase">{{ $why['eyebrow'] }}</p>
                    @endif
                    <h2 id="home-why" class="mt-3 text-2xl font-semibold tracking-tight sm:text-4xl">{{ $why['heading'] }}</h2>
                </div>
                @if (! empty($why['items']))
                    <ul class="grid gap-5 sm:grid-cols-3 lg:col-span-8">
                        @foreach ($why['items'] as $item)
                            <li>
                                @if ($item['image_url'])
                                    <img src="{{ $item['image_url'] }}" alt="" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover">
                                @else
                                    <i class="fa-solid {{ $item['icon'] }} text-2xl text-white" aria-hidden="true"></i>
                                @endif
                                <h3 class="mt-4 font-semibold">{{ $item['heading'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-white/75">{{ $item['text'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endif

    {{-- Projects --}}
    @if ($showProjects)
        <section aria-labelledby="home-work" class="section">
            <div class="container-site">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        @if (filled($projectsSection['eyebrow']))
                            <p class="eyebrow">{{ $projectsSection['eyebrow'] }}</p>
                        @endif
                        <h2 id="home-work" class="mt-3 text-2xl font-semibold tracking-tight sm:text-4xl">{{ $projectsSection['heading'] }}</h2>
                    </div>
                    @if (filled($projectsSection['link_label']))
                        <a href="{{ route('projects.index') }}" class="link-arrow">{{ $projectsSection['link_label'] }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    @endif
                </div>
                <div class="section-body grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProjects as $project)
                        @include('projects.partials.card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Space the CTA away from the band above it when no projects section sits between them. --}}
    @if ($cta['enabled'])
        <x-cta-band :title="$cta['title']" :text="$cta['text']" class="{{ ! $showProjects ? 'section-top' : '' }}" />
    @endif
</x-layouts.site>
