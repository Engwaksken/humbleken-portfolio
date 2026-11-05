<x-layouts.site :title="$pageTitle.' — '.$brandName" :description="$pageIntro">
    <x-page-hero eyebrow="Approach" :title="$pageTitle" :intro="$pageIntro" />

    <section aria-label="Steps" class="container-site py-14 sm:py-20">
        <ol class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach (array_values(array_filter(preg_split('/\r\n|\r|\n/', $approachSteps))) as $index => $step)
                <li class="card relative overflow-hidden p-7">
                    <span aria-hidden="true" class="absolute -top-3 -right-1 text-8xl font-bold text-brand-tint">{{ $index + 1 }}</span>
                    <span class="relative inline-flex items-center gap-2 rounded-full bg-brand px-3 py-1 text-xs font-bold text-white">Step {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <p class="relative mt-5 text-lg leading-8">{{ $step }}</p>
                </li>
            @endforeach
        </ol>
        <a class="btn-primary mt-10" href="{{ route('booking') }}">Request a consultation</a>
    </section>
</x-layouts.site>
