<x-layouts.site :title="$project->title.' — '.$brandName" :description="$project->summary">
    <article aria-labelledby="project-title" class="mx-auto max-w-3xl px-6 py-20 lg:py-24">
        <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-[#087e8b] hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]">
            <span aria-hidden="true">←</span> All projects
        </a>

        <h1 id="project-title" class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl">{{ $project->title }}</h1>

        @if ($project->summary)
            <p class="mt-5 text-lg leading-8 text-[#506176]">{{ $project->summary }}</p>
        @endif

        @if (! empty($project->technologies))
            <ul class="mt-6 flex flex-wrap gap-2" aria-label="Technologies">
                @foreach ($project->technologies as $technology)
                    <li class="rounded bg-[#e8f2f6] px-2 py-1 text-xs font-medium">{{ $technology }}</li>
                @endforeach
            </ul>
        @endif

        @if ($project->body)
            <div class="mt-10 border-t border-[#14263d]/10 pt-10 leading-8 text-[#14263d] [&_a]:text-[#087e8b] [&_a]:underline [&_code]:rounded [&_code]:bg-[#e8f2f6] [&_code]:px-1 [&_h2]:mt-10 [&_h2]:text-2xl [&_h2]:font-semibold [&_h3]:mt-8 [&_h3]:text-xl [&_h3]:font-semibold [&_li]:mt-2 [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mt-5 [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-6">
                {{ $project->bodyHtml() }}
            </div>
        @endif

        <div class="mt-14 border-l-2 border-[#087e8b] pl-4">
            <p class="font-semibold">Working on something similar?</p>
            <a href="{{ route('home') }}#contact" class="mt-2 inline-block text-sm font-semibold text-[#087e8b] underline underline-offset-4 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]">Send an enquiry</a>
        </div>
    </article>
</x-layouts.site>
