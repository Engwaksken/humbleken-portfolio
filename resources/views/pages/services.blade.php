<x-layouts.site :title="$pageTitle.' — '.$brandName" :description="$pageIntro">
    <x-page-hero eyebrow="Services" :title="$pageTitle" :intro="$pageIntro">
        <a class="btn-primary mt-6" href="{{ route('booking') }}">Request a consultation</a>
    </x-page-hero>

    <div class="container-site section stack-lg">
        @forelse ($categories as $category)
            @if ($servicesByCategory->has($category->id))
                <section aria-labelledby="category-{{ $category->id }}">
                    <h2 id="category-{{ $category->id }}" class="flex items-center gap-4 text-2xl font-semibold tracking-tight">
                        {{ $category->name }}
                        <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                    </h2>
                    <div class="section-body grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($servicesByCategory[$category->id] as $service)
                            @include('pages.partials.service-card')
                        @endforeach
                    </div>
                </section>
            @endif
        @empty
            @if ($services->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        @include('pages.partials.service-card')
                    @endforeach
                </div>
            @else
                <p class="card p-8 text-center text-muted">Services are being updated. Please contact us to discuss your requirements.</p>
            @endif
        @endforelse

        @if ($services->isNotEmpty() && $categories->isNotEmpty() && $services->whereNull('service_category_id')->isNotEmpty())
            <section aria-labelledby="category-other">
                <h2 id="category-other" class="flex items-center gap-4 text-2xl font-semibold tracking-tight">Other services <span class="h-px flex-1 bg-line" aria-hidden="true"></span></h2>
                <div class="section-body grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services->whereNull('service_category_id') as $service)
                        @include('pages.partials.service-card')
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <x-cta-band />
</x-layouts.site>
