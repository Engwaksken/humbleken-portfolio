<article class="flex flex-col gap-3 border-t-2 border-brand bg-white p-6">
    @if ($project->sampleImageUrl())
        <img src="{{ $project->sampleImageUrl() }}" alt="Sample image for {{ $project->title }}" loading="lazy" decoding="async" class="-mx-6 -mt-6 mb-2 aspect-video w-[calc(100%+3rem)] object-cover">
    @endif
    <h3 class="text-lg font-semibold">
        <a href="{{ route('projects.show', ['project' => $project->slug]) }}" class="hover:text-brand focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">{{ $project->title }}</a>
    </h3>
    @if ($project->summary)
        <p class="leading-7 text-[#506176]">{{ $project->summary }}</p>
    @endif
    @if (! empty($project->technologies))
        <ul class="mt-auto flex flex-wrap gap-2 pt-2" aria-label="Technologies">
            @foreach ($project->technologies as $technology)
                <li class="rounded bg-brand-tint px-2 py-1 text-xs font-medium">{{ $technology }}</li>
            @endforeach
        </ul>
    @endif
</article>
