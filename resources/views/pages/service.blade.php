<x-layouts.site :title="$service->name.' — '.$brandName" :description="$service->summary ?: $service->description">
    <x-page-hero :eyebrow="$service->category?->name ?? 'Service'" :title="$service->name" :intro="$service->summary">
        <x-slot:before>
            <a href="{{ route('services.index') }}" class="link-arrow mb-8 flex w-fit"><i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> All services</a>
        </x-slot:before>
    </x-page-hero>

    <article class="container-site py-12 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-14">
            <div class="min-w-0 lg:col-span-8">
                <h2 class="text-2xl font-semibold tracking-tight">Overview</h2>
                <div class="mt-4 text-[1.0625rem] leading-8 whitespace-pre-line text-muted">{{ $service->description }}</div>
                @if ($service->sampleImageUrl())
                    <img class="mt-10 max-h-[32rem] w-full rounded-2xl border border-line object-cover" src="{{ $service->sampleImageUrl() }}" alt="Sample image for {{ $service->name }}" loading="lazy">
                @endif
                @if ($service->sampleVideoEmbedUrl())
                    <div class="mt-8 aspect-video overflow-hidden rounded-2xl bg-ink"><iframe src="{{ $service->sampleVideoEmbedUrl() }}" title="Video sample for {{ $service->name }}" loading="lazy" allowfullscreen class="h-full w-full"></iframe></div>
                @endif
            </div>
            <aside class="lg:col-span-4" aria-label="Service details">
                <div class="space-y-5 lg:sticky lg:top-28">
                    @if ($service->delivery_method || $service->estimated_duration || $service->indicative_price)
                        <dl class="card divide-y divide-line">
                            @foreach ([['Delivery', $service->delivery_method], ['Estimated duration', $service->estimated_duration], ['Indicative price', $service->indicative_price]] as [$term, $value])
                                @if ($value)
                                    <div class="p-5">
                                        <dt class="text-xs font-bold tracking-[.14em] text-muted uppercase">{{ $term }}</dt>
                                        <dd class="mt-1.5 font-semibold">{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif
                    <div class="rounded-2xl bg-secondary p-6 text-white">
                        <p class="font-semibold">Interested in this service?</p>
                        <p class="mt-2 text-sm leading-6 text-white/75">Request a consultation and we will confirm availability.</p>
                        <a class="btn-light mt-5 w-full" href="{{ route('booking', ['service' => $service->id]) }}">Request this service</a>
                    </div>
                </div>
            </aside>
        </div>
    </article>
</x-layouts.site>
