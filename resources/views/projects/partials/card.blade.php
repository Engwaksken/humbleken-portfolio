<article class="card-hover group relative flex flex-col overflow-hidden">
    <div class="aspect-[16/10] overflow-hidden bg-[linear-gradient(135deg,var(--color-brand-tint),var(--color-secondary-tint))]">
        @if ($project->sampleImageUrl())
            <img src="{{ $project->sampleImageUrl() }}" alt="" loading="lazy" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @else
            <div class="grid size-full place-items-center" aria-hidden="true">
                <span class="text-5xl font-bold tracking-tight text-brand/25">{{ mb_strtoupper(mb_substr($project->title, 0, 2)) }}</span>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6">
        <h3 class="text-lg font-semibold leading-snug">
            {{-- The stretched link makes the whole card clickable while keeping one accessible link. --}}
            <a href="{{ route('projects.show', ['project' => $project->slug]) }}" class="transition after:absolute after:inset-0 after:content-[''] group-hover:text-brand-dark">{{ $project->title }}</a>
        </h3>
        @if ($project->summary)
            <p class="mt-2.5 line-clamp-3 leading-7 text-muted">{{ $project->summary }}</p>
        @endif
        @if (! empty($project->technologies))
            <ul class="mt-auto flex flex-wrap gap-1.5 pt-5" aria-label="Technologies">
                @foreach (array_slice($project->technologies, 0, 4) as $technology)
                    <li class="chip">{{ $technology }}</li>
                @endforeach
                @if (count($project->technologies) > 4)
                    <li class="chip bg-secondary-tint text-ink">+{{ count($project->technologies) - 4 }}</li>
                @endif
            </ul>
        @endif
        <span class="link-arrow mt-5" aria-hidden="true">View case study →</span>
    </div>
</article>
