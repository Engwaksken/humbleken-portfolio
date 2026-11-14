<article class="card-hover group relative flex flex-col overflow-hidden">
    @if ($service->sampleImageUrl())
        <div class="aspect-[16/9] overflow-hidden border-b border-line bg-canvas">
            <img src="{{ $service->sampleImageUrl() }}" alt="" loading="lazy" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        </div>
    @endif
    <div class="flex flex-1 flex-col p-6 sm:p-7">
        @unless ($service->sampleImageUrl())
            <i class="mb-5 fa-solid fa-screwdriver-wrench text-2xl text-brand" aria-hidden="true"></i>
        @endunless
        <h3 class="text-lg font-semibold">
            <a class="after:absolute after:inset-0 after:content-[''] group-hover:text-brand-dark" href="{{ route('services.show', $service->slug) }}">{{ $service->name }}</a>
        </h3>
        <p class="mt-2.5 line-clamp-4 leading-7 text-muted">{{ $service->summary ?: $service->description }}</p>
        @if ($service->indicative_price || $service->estimated_duration)
            <p class="mt-4 flex flex-wrap gap-2 text-xs">
                @if ($service->estimated_duration)<span class="chip bg-secondary-tint text-ink">{{ $service->estimated_duration }}</span>@endif
                @if ($service->indicative_price)<span class="chip">{{ $service->formattedPrice() }}</span>@endif
            </p>
        @endif
        <span class="link-arrow mt-auto pt-6" aria-hidden="true">Service details <i class="fa-solid fa-arrow-right text-xs"></i></span>
    </div>
</article>
