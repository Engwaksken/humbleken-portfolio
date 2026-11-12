<x-layouts.site :title="$page['title'].' — '.$brandName" :description="$page['intro']">
    <x-page-hero :eyebrow="$page['eyebrow']" :title="$page['title']" :intro="$page['intro']" :image="$page['image_url']" :image-alt="$page['image_alt']" />

    <section aria-label="Steps" class="container-site section">
        <ol class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($page['steps'] as $index => $step)
                <li class="card overflow-hidden">
                    @if ($step['image_url'])
                        <img src="{{ $step['image_url'] }}" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover">
                    @endif
                    <div class="p-7">
                        <p class="text-sm font-bold tracking-widest text-brand">Step {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                        @if ($step['title'])
                            <h2 class="mt-3 text-xl font-semibold">{{ $step['title'] }}</h2>
                        @endif
                        <p @class(['text-lg leading-8', 'mt-4' => ! $step['title'], 'mt-2 text-muted' => $step['title']])>{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        <a class="btn-primary mt-8" href="{{ route('booking') }}">Request a consultation</a>
    </section>

    <x-page-extras :page="$page" />
</x-layouts.site>
