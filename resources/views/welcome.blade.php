<x-layouts.site :title="$brandName.' — '.$tagline">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-line bg-white">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_85%_10%,var(--color-brand-tint),transparent_55%)]"></div>
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgb(20_38_61/0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgb(20_38_61/0.04)_1px,transparent_1px)] bg-[size:3rem_3rem] [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)]"></div>
        <div class="container-site relative grid gap-12 py-16 sm:py-24 lg:grid-cols-12 lg:items-center lg:gap-16 lg:py-28">
            <div class="lg:col-span-7">
                <p class="inline-flex items-center gap-2 rounded-full border border-brand/20 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-brand-dark shadow-sm">
                    <span class="size-2 rounded-full bg-brand" aria-hidden="true"></span>{{ $tagline }}
                </p>
                <h1 class="mt-6 text-[clamp(2.25rem,6vw,4.25rem)] leading-[1.05] font-semibold tracking-tight text-balance">{{ $homeTitle }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-muted text-pretty">{{ $homeIntro }}</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('services.index') }}" class="btn-primary">Explore services <span aria-hidden="true">→</span></a>
                    <a href="{{ route('booking') }}" class="btn-ghost">Book a consultation</a>
                </div>
            </div>
            <aside class="lg:col-span-5" aria-label="How to get started">
                <div class="card relative p-6 sm:p-8">
                    <p class="eyebrow">Start with the challenge</p>
                    <p class="mt-4 text-xl leading-snug font-medium sm:text-2xl">Describe the issue or objective. We can clarify the requirements and identify a suitable path forward.</p>
                    <ol class="mt-7 space-y-4 border-t border-line pt-6 text-sm">
                        @foreach (['Share the problem or goal', 'Get a clear, jargon-free assessment', 'Move forward with a practical plan'] as $i => $step)
                            <li class="flex items-center gap-3">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-tint text-xs font-bold text-brand-dark">{{ $i + 1 }}</span>
                                <span class="font-medium">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <a href="{{ route('contact') }}" class="link-arrow mt-7">Send an enquiry <span aria-hidden="true">→</span></a>
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
                    <a href="{{ route('services.index') }}" class="link-arrow">All services <span aria-hidden="true">→</span></a>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredServices as $service)
                        <a href="{{ route('services.show', $service->slug) }}" class="card-hover group flex flex-col p-6 sm:p-7">
                            <span class="grid size-12 place-items-center rounded-2xl bg-brand-tint text-lg font-bold text-brand-dark transition group-hover:bg-brand group-hover:text-white" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-6 text-xl font-semibold">{{ $service->name }}</h3>
                            <p class="mt-3 line-clamp-4 leading-7 text-muted">{{ $service->summary ?: $service->description }}</p>
                            <span class="link-arrow mt-auto pt-6">Learn more <span aria-hidden="true">→</span></span>
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
                <p class="inline-flex items-center gap-2 text-xs font-bold tracking-[.18em] text-white/70 uppercase"><span class="h-px w-6 bg-current"></span>Why work with us</p>
                <h2 id="home-why" class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Engineering that is clear, careful and practical.</h2>
            </div>
            <ul class="grid gap-5 sm:grid-cols-3 lg:col-span-8">
                @foreach ([
                    ['Clear communication', 'Technical findings explained in plain language, so you can make confident decisions.', 'M4 6h16v10H8l-4 4z'],
                    ['Proportionate solutions', 'Recommendations sized to the problem and your budget — no unnecessary complexity.', 'M4 20h16M7 16V9m5 7V5m5 11v-4'],
                    ['Reliable follow-through', 'Practical engineering support from diagnosis through to a working result.', 'M5 12l4 4 10-10'],
                ] as [$heading, $text, $icon])
                    <li class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <span class="grid size-11 place-items-center rounded-xl bg-white/10" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="{{ $icon }}" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <h3 class="mt-5 font-semibold">{{ $heading }}</h3>
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
                    <a href="{{ route('projects.index') }}" class="link-arrow">All projects <span aria-hidden="true">→</span></a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProjects as $project)
                        @include('projects.partials.card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band class="pt-16 sm:pt-24" />
</x-layouts.site>
