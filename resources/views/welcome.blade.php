<x-layouts.site :title="$brandName.' — '.$tagline">
    <section class="border-b border-[#14263d]/10 bg-gradient-to-br from-white to-brand-tint">
        <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 sm:py-28 lg:grid-cols-[1.4fr_.6fr] lg:items-end lg:px-8">
            <div>
                <p class="mb-7 text-xs font-bold uppercase tracking-[.2em] text-brand">{{ $tagline }}</p>
                <h1 class="max-w-4xl text-5xl font-semibold leading-tight tracking-tight sm:text-6xl">{{ $homeTitle }}</h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-[#555750]">{{ $homeIntro }}</p>
                <a href="{{ route('services.index') }}" class="mt-9 inline-flex border-b-2 border-brand pb-2 text-sm font-semibold">Explore services <span class="ml-3" aria-hidden="true">→</span></a>
            </div>
            <aside class="border-l-2 border-brand pl-6"><p class="text-sm font-semibold uppercase tracking-wider text-brand">Start with the challenge</p><p class="mt-4 text-2xl font-medium leading-snug">Describe the issue or objective. We can clarify the requirements and identify a suitable path forward.</p></aside>
        </div>
    </section>
    @if ($featuredServices->isNotEmpty())
        <section class="border-b border-[#14263d]/10 bg-white"><div class="mx-auto max-w-6xl px-6 py-16 lg:px-8"><div class="flex items-end justify-between"><h2 class="text-3xl font-semibold">Services</h2><a href="{{ route('services.index') }}" class="text-sm font-semibold text-brand underline">All services</a></div><div class="mt-8 grid gap-6 md:grid-cols-3">@foreach ($featuredServices as $service)<a href="{{ route('services.show', $service->slug) }}" class="border-t-2 border-brand py-5"><h3 class="text-xl font-semibold">{{ $service->name }}</h3><p class="mt-3 leading-7 text-[#506176]">{{ $service->summary ?: $service->description }}</p></a>@endforeach</div></div></section>
    @endif
    @if ($featuredProjects->isNotEmpty())
        <section><div class="mx-auto max-w-6xl px-6 py-16 lg:px-8"><div class="flex items-end justify-between"><h2 class="text-3xl font-semibold">Recent work</h2><a href="{{ route('projects.index') }}" class="text-sm font-semibold text-brand underline">All projects</a></div><div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($featuredProjects as $project) @include('projects.partials.card') @endforeach</div></div></section>
    @endif
</x-layouts.site>
