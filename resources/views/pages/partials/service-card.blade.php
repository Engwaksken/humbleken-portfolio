<article class="card-hover group relative flex flex-col p-6 sm:p-7">
    <span class="grid size-11 place-items-center rounded-xl bg-brand-tint text-brand-dark transition group-hover:bg-brand group-hover:text-white" aria-hidden="true">
        <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L4 17l3 3 5.3-5.3a4 4 0 0 0 5.4-5.4l-2.4 2.4-2.6-.4-.4-2.6z" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
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
    <span class="link-arrow mt-auto pt-6" aria-hidden="true">Service details →</span>
</article>
