<article class="flex flex-col gap-3 border-t-2 border-[#087e8b] bg-white p-6">
    <h3 class="text-lg font-semibold">
        <a href="{{ route('projects.show', ['project' => $project->slug]) }}" class="hover:text-[#087e8b] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]">{{ $project->title }}</a>
    </h3>
    @if ($project->summary)
        <p class="leading-7 text-[#506176]">{{ $project->summary }}</p>
    @endif
    @if (! empty($project->technologies))
        <ul class="mt-auto flex flex-wrap gap-2 pt-2" aria-label="Technologies">
            @foreach ($project->technologies as $technology)
                <li class="rounded bg-[#e8f2f6] px-2 py-1 text-xs font-medium">{{ $technology }}</li>
            @endforeach
        </ul>
    @endif
</article>
