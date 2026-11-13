@php
    // One collapsible group per category that has active services, then any uncategorised ones.
    $groups = $categories
        ->filter(fn ($category) => $servicesByCategory->has($category->id))
        ->map(fn ($category) => [
            'id' => 'category-'.$category->slug,
            'name' => $category->name,
            'description' => $category->description,
            'services' => $servicesByCategory[$category->id],
        ])
        ->values();
    // Services with no category, or whose category is hidden, are listed together.
    $uncategorised = $services->reject(fn ($service) => $service->service_category_id && $categories->contains('id', $service->service_category_id));
    if ($uncategorised->isNotEmpty()) {
        $groups->push(['id' => 'category-other', 'name' => $groups->isEmpty() ? 'Services' : 'Other services', 'description' => null, 'services' => $uncategorised]);
    }
@endphp
<x-layouts.site :title="$page['title'].' | '.$brandName" :description="$page['intro']">
    <x-page-hero :eyebrow="$page['eyebrow']" :title="$page['title']" :intro="$page['intro']" :intro-lines="$page['intro_lines']" :image="$page['image_url']" :image-alt="$page['image_alt']">
        <a class="btn-primary mt-6" href="{{ route('booking') }}">Request a consultation <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
    </x-page-hero>

    <div class="container-site section">
        @if ($groups->isEmpty())
            <p class="card p-6 text-center text-muted sm:p-8">Services are being updated. Please contact me to discuss what you need.</p>
        @elseif ($groups->count() === 1)
            {{-- A single group needs no collapsing. --}}
            @if ($groups->first()['id'] !== 'category-other')
                <h2 class="flex items-center gap-4 text-2xl font-semibold tracking-tight">
                    {{ $groups->first()['name'] }}
                    <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                </h2>
            @endif
            <div class="section-body grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($groups->first()['services'] as $service)
                    @include('pages.partials.service-card')
                @endforeach
            </div>
        @else
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted">
                    {{ trans_choice('{1} 1 service|[2,*] :count services', $services->count()) }} in {{ $groups->count() }} areas. Open an area to see its services.
                </p>
                <button type="button" class="btn-ghost w-full sm:w-auto" data-services-toggle-all aria-controls="service-groups" hidden>
                    <i class="fa-solid fa-up-right-and-down-left-from-center text-xs" aria-hidden="true" data-toggle-all-icon></i>
                    <span data-toggle-all-label>Expand all</span>
                </button>
            </div>

            {{-- Quick links: jump to an area and open it. --}}
            <nav aria-label="Service areas" class="-mx-4 mt-5 overflow-x-auto px-4 pb-1">
                <ul class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                    @foreach ($groups as $group)
                        <li>
                            <a href="#{{ $group['id'] }}" data-open-group="{{ $group['id'] }}" class="inline-flex min-h-9 items-center gap-2 rounded-full border border-line bg-white px-3.5 text-sm font-medium transition hover:border-brand hover:text-brand-dark">
                                {{ $group['name'] }}
                                <span class="rounded-full bg-brand-tint px-1.5 text-xs font-semibold text-brand-dark">{{ $group['services']->count() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div id="service-groups" class="mt-6 space-y-4 sm:mt-8">
                @foreach ($groups as $group)
                    <details id="{{ $group['id'] }}" class="service-group group/acc card overflow-hidden scroll-mt-28" @if ($loop->first) open @endif>
                        {{-- Direct children only (spans and one heading) so the summary stays valid HTML. --}}
                        <summary class="grid cursor-pointer list-none grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 p-5 transition hover:bg-canvas/70 sm:p-6 [&::-webkit-details-marker]:hidden">
                            <span class="row-span-2 grid size-11 place-items-center rounded-xl bg-brand-tint text-sm font-bold text-brand transition group-open/acc:bg-brand group-open/acc:text-white" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h2 class="text-lg font-semibold tracking-tight sm:text-xl">{{ $group['name'] }}</h2>
                            <span class="row-span-2 grid size-9 place-items-center rounded-full border border-line text-muted transition duration-300 group-open/acc:rotate-180 group-open/acc:border-brand group-open/acc:text-brand" aria-hidden="true">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </span>
                            <span class="col-start-2 text-sm leading-6 text-muted">
                                <span class="font-semibold">{{ trans_choice('{1} 1 service|[2,*] :count services', $group['services']->count()) }}</span>
                                @if (filled($group['description']))
                                    <span class="max-sm:hidden"> · {{ $group['description'] }}</span>
                                @endif
                            </span>
                        </summary>
                        <div class="service-group-body border-t border-line bg-canvas/60 p-4 sm:p-6">
                            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                                @foreach ($group['services'] as $service)
                                    @include('pages.partials.service-card')
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>

    <x-page-extras :page="$page" />

    <x-cta-band />
</x-layouts.site>
