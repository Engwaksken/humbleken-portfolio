<x-layouts.site :title="$project->title.' | '.$brandName" :description="$project->summary">
    <x-page-hero eyebrow="Case study" :title="$project->title" :intro="$project->summary" title-id="project-title">
        <x-slot:before>
            <a href="{{ route('projects.index') }}" class="link-arrow mb-8 flex w-fit"><i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> All projects</a>
        </x-slot:before>
    </x-page-hero>

    <article aria-labelledby="project-title" class="container-site section">
        <div class="grid gap-layout lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8">
                @if ($project->sampleImageUrl())
                    <img src="{{ $project->sampleImageUrl() }}" alt="Sample image for {{ $project->title }}" loading="lazy" decoding="async" class="w-full rounded-2xl border border-line object-cover">
                @endif
                @if ($project->sampleVideoEmbedUrl())
                    <div class="mt-8 aspect-video overflow-hidden rounded-2xl bg-ink first:mt-0">
                        <iframe src="{{ $project->sampleVideoEmbedUrl() }}" title="Video sample for {{ $project->title }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen class="h-full w-full"></iframe>
                    </div>
                @endif

                @if ($project->body)
                    <div class="markdown-body mt-8 first:mt-0">
                        {{ $project->bodyHtml() }}
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-4" aria-label="Project details">
                <div class="space-y-5 lg:sticky lg:top-24">
                    @if (! empty($project->technologies))
                        <div class="card p-5 sm:p-6">
                            <h2 class="text-xs font-bold tracking-[.14em] text-muted uppercase">Technologies</h2>
                            <ul class="mt-4 flex flex-wrap gap-2" aria-label="Technologies">
                                @foreach ($project->technologies as $technology)
                                    <li class="chip">{{ $technology }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="rounded-2xl bg-secondary p-6 text-white">
                        <p class="font-semibold">Working on something similar?</p>
                        <p class="mt-2 text-sm leading-6 text-white/75">Share what you need and I will suggest a practical approach.</p>
                        <a href="{{ route('contact') }}" class="btn-light mt-5 w-full">Send an enquiry</a>
                    </div>
                </div>
            </aside>
        </div>
    </article>
</x-layouts.site>
