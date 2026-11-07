<x-layouts.site :title="$pageTitle.' — '.$brandName" :description="$pageIntro">
    <x-page-hero eyebrow="Approach" :title="$pageTitle" :intro="$pageIntro" />

    <section aria-label="Steps" class="container-site section">
        <ol class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach (array_values(array_filter(preg_split('/\r\n|\r|\n/', $approachSteps))) as $index => $step)
                <li class="card p-7">
                    <p class="text-sm font-bold tracking-widest text-brand">Step {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                    <p class="mt-4 text-lg leading-8">{{ $step }}</p>
                </li>
            @endforeach
        </ol>
        <a class="btn-primary mt-8" href="{{ route('booking') }}">Request a consultation</a>
    </section>
</x-layouts.site>
