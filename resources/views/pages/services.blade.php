<x-layouts.site :title="$pageTitle.' — '.$brandName" :description="$pageIntro">
    <section class="mx-auto max-w-6xl px-6 py-16 lg:px-8"><p class="text-sm font-semibold uppercase tracking-wider text-brand">Services</p><h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight sm:text-5xl">{{ $pageTitle }}</h1><p class="mt-5 max-w-3xl text-lg leading-8 text-[#506176]">{{ $pageIntro }}</p>
        @forelse ($categories as $category)
            @if ($servicesByCategory->has($category->id))<section class="mt-14"><h2 class="border-b border-[#14263d]/15 pb-4 text-2xl font-semibold">{{ $category->name }}</h2><div class="divide-y divide-[#14263d]/10">@foreach ($servicesByCategory[$category->id] as $service) @include('pages.partials.service-card') @endforeach</div></section>@endif
        @empty
            @if ($services->isNotEmpty())<div class="mt-12 divide-y divide-[#14263d]/10">@foreach ($services as $service) @include('pages.partials.service-card') @endforeach</div>@else<p class="mt-10 text-[#506176]">Services are being updated. Please contact us to discuss your requirements.</p>@endif
        @endforelse
        @if ($services->isNotEmpty() && $categories->isNotEmpty()) @foreach ($services->whereNull('service_category_id') as $service) @include('pages.partials.service-card') @endforeach @endif
        <a class="mt-12 inline-flex rounded bg-brand px-5 py-3 font-semibold text-white" href="{{ route('booking') }}">Request a consultation</a>
    </section>
</x-layouts.site>
