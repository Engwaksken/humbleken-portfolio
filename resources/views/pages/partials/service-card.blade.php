<article class="card-hover group relative flex flex-col p-6 sm:p-7">
    <i class="fa-solid fa-screwdriver-wrench text-2xl text-brand" aria-hidden="true"></i>
    <h3 class="mt-5 text-lg font-semibold">
        <a class="after:absolute after:inset-0 after:content-[''] group-hover:text-brand-dark" href="{{ route('services.show', $service->slug) }}">{{ $service->name }}</a>
    </h3>
    <p class="mt-2.5 line-clamp-4 leading-7 text-muted">{{ $service->summary ?: $service->description }}</p>
    @if ($service->indicative_price || $service->estimated_duration)
        <p class="mt-4 flex flex-wrap gap-2 text-xs">
            @if ($service->estimated_duration)<span class="chip bg-secondary-tint text-ink">{{ $service->estimated_duration }}</span>@endif
            @if ($service->indicative_price)<span class="chip">{{ $service->indicative_price }}</span>@endif
        </p>
    @endif
    <span class="link-arrow mt-auto pt-6" aria-hidden="true">Service details <i class="fa-solid fa-arrow-right text-xs"></i></span>
</article>
