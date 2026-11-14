@php
    $imageUrl = $service->sampleImageUrl();
    $heroPosition = $imageUrl ? $service->featuredImagePosition()->heroPosition() : null;
@endphp
<x-layouts.site :title="$service->name.' | '.$brandName" :description="$service->summary ?: $service->description" :og-image="$imageUrl">
    <x-page-hero :eyebrow="$service->category?->name ?? 'Service'" :title="$service->name" :intro="$service->summary"
        :image="$heroPosition ? $imageUrl : null" :image-alt="$service->name" :image-position="$heroPosition">
        <x-slot:before>
            <a href="{{ route('services.index') }}" class="link-arrow mb-8 flex w-fit"><i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> All services</a>
        </x-slot:before>
        <a class="btn-primary mt-6" href="{{ route('booking', ['service' => $service->id]) }}">Request this service <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
    </x-page-hero>

    @if ($imageUrl && $heroPosition === null)
        <figure class="container-site mt-8 sm:mt-12">
            <img src="{{ $imageUrl }}" alt="{{ $service->name }}" fetchpriority="high" decoding="async" class="aspect-[16/9] w-full rounded-2xl border border-line object-cover lg:aspect-[21/9]">
        </figure>
    @endif

    <article class="container-site section">
        <div class="grid gap-layout lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8">
                <h2 class="text-2xl font-semibold tracking-tight">Overview</h2>
                <div class="mt-4 text-[1.0625rem] leading-8 whitespace-pre-line text-muted">{{ $service->description }}</div>
                @if ($service->sampleVideoEmbedUrl())
                    <div class="mt-8 aspect-video overflow-hidden rounded-2xl bg-ink"><iframe src="{{ $service->sampleVideoEmbedUrl() }}" title="Video about {{ $service->name }}" loading="lazy" allowfullscreen class="h-full w-full"></iframe></div>
                @endif
            </div>
            <aside class="lg:col-span-4" aria-label="Service details">
                <div class="space-y-5 lg:sticky lg:top-24">
                    @if ($service->delivery_method || $service->estimated_duration || $service->indicative_price)
                        <dl class="card divide-y divide-line">
                            @foreach ([['Delivery', $service->delivery_method], ['Estimated duration', $service->estimated_duration], ['Indicative price', $service->formattedPrice()]] as [$term, $value])
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
                        <p class="mt-2 text-sm leading-6 text-white/75">Send me a request with a time that suits you and I will confirm my availability.</p>
                        <a class="btn-light mt-5 w-full" href="{{ route('booking', ['service' => $service->id]) }}">Request this service</a>
                    </div>
                </div>
            </aside>
        </div>
    </article>
</x-layouts.site>
