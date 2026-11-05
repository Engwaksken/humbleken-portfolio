<x-layouts.site :title="$brandName.' — '.$tagline">
    {{-- Hero --}}
    <section class="border-b border-line bg-white">
        <div class="container-site grid gap-12 py-16 sm:py-24 lg:grid-cols-12 lg:items-center lg:gap-16 lg:py-28">
            <div class="lg:col-span-7">
                <p class="eyebrow">{{ $tagline }}</p>
                <h1 class="mt-6 text-[clamp(2.25rem,6vw,4.25rem)] leading-[1.05] font-semibold tracking-tight text-balance">{{ $homeTitle }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-muted text-pretty">{{ $homeIntro }}</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('services.index') }}" class="btn-primary">Explore services <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    <a href="{{ route('booking') }}" class="btn-ghost">Book a consultation</a>
                </div>
            </div>
            <aside class="lg:col-span-5" aria-label="How to get started">
                <div class="card p-6 sm:p-8">
                    <p class="eyebrow">Start with the challenge</p>
                    <p class="mt-4 text-xl leading-snug font-medium sm:text-2xl">Describe the issue or objective. We can clarify the requirements and identify a suitable path forward.</p>
                    <ol class="mt-7 space-y-4 border-t border-line pt-6 text-sm">
                        @foreach (['Share the problem or goal', 'Get a clear, jargon-free assessment', 'Move forward with a practical plan'] as $i => $step)
                            <li class="flex items-center gap-3">
                                <span class="w-5 shrink-0 text-sm font-bold text-brand">{{ $i + 1 }}.</span>
                                <span class="font-medium">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <a href="{{ route('contact') }}" class="link-arrow mt-7">Send an enquiry <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </div>
            </aside>
        </div>
    </section>

    {{-- Services --}}
    @if ($featuredServices->isNotEmpty())
        <section aria-labelledby="home-services" class="py-16 sm:py-24">
            <div class="container-site">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="eyebrow">What we do</p>
                        <h2 id="home-services" class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Services</h2>
                    </div>
                    <a href="{{ route('services.index') }}" class="link-arrow">All services <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredServices as $service)
                        <a href="{{ route('services.show', $service->slug) }}" class="card-hover group flex flex-col p-6 sm:p-7">
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
    <section aria-labelledby="home-why" class="bg-secondary py-16 text-white sm:py-24">
        <div class="container-site grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="text-xs font-bold tracking-[.18em] text-white/70 uppercase">Why work with us</p>
                <h2 id="home-why" class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Engineering that is clear, careful and practical.</h2>
            </div>
            <ul class="grid gap-5 sm:grid-cols-3 lg:col-span-8">
                @foreach ([
                    ['Clear communication', 'Technical findings explained in plain language, so you can make confident decisions.', 'fa-comments'],
                    ['Proportionate solutions', 'Recommendations sized to the problem and your budget — no unnecessary complexity.', 'fa-scale-balanced'],
                    ['Reliable follow-through', 'Practical engineering support from diagnosis through to a working result.', 'fa-circle-check'],
                ] as [$heading, $text, $icon])
                    <li>
                        <i class="fa-solid {{ $icon }} text-2xl text-white" aria-hidden="true"></i>
                        <h3 class="mt-4 font-semibold">{{ $heading }}</h3>
                        <p class="mt-2 text-sm leading-6 text-white/75">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Projects --}}
    @if ($featuredProjects->isNotEmpty())
        <section aria-labelledby="home-work" class="py-16 sm:py-24">
            <div class="container-site">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="eyebrow">Portfolio</p>
                        <h2 id="home-work" class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Recent work</h2>
                    </div>
                    <a href="{{ route('projects.index') }}" class="link-arrow">All projects <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProjects as $project)
                        @include('projects.partials.card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Space the CTA away from the dark band when no projects section sits between them. --}}
    <x-cta-band class="{{ $featuredProjects->isEmpty() ? 'mt-16 sm:mt-24' : '' }}" />
</x-layouts.site>
