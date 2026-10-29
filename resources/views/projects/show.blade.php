<x-layouts.site :title="$project->title.' — '.$brandName" :description="$project->summary">
    <article aria-labelledby="project-title" class="mx-auto max-w-3xl px-6 py-20 lg:py-24">
        <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">
            <span aria-hidden="true">←</span> All projects
        </a>

        <h1 id="project-title" class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl">{{ $project->title }}</h1>

        @if ($project->summary)
            <p class="mt-5 text-lg leading-8 text-[#506176]">{{ $project->summary }}</p>
        @endif

        @if (! empty($project->technologies))
            <ul class="mt-6 flex flex-wrap gap-2" aria-label="Technologies">
                @foreach ($project->technologies as $technology)
                    <li class="rounded bg-brand-tint px-2 py-1 text-xs font-medium">{{ $technology }}</li>
                @endforeach
            </ul>
        @endif

        @if ($project->body)
            <div class="markdown-body mt-10 border-t border-[#14263d]/10 pt-10">
                {{ $project->bodyHtml() }}
            </div>
        @endif

        <div class="mt-14 border-l-2 border-brand pl-4">
            <p class="font-semibold">Working on something similar?</p>
            <a href="{{ route('home') }}#contact" class="mt-2 inline-block text-sm font-semibold text-brand underline underline-offset-4 hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">Send an enquiry</a>
        </div>
    </article>
</x-layouts.site>
