<x-layouts.site :title="'Projects — '.$brandName" description="Selected projects and case studies.">
    <section aria-labelledby="projects-title" class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
        <p class="text-sm font-semibold uppercase tracking-[.16em] text-brand">Projects</p>
        <h1 id="projects-title" class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">Selected work and case studies.</h1>

        @if ($projects->isEmpty())
            <p class="mt-10 border-l-2 border-brand py-2 pl-4 text-[#506176]">Projects are being added.</p>
        @else
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    @include('projects.partials.card')
                @endforeach
            </div>

            <div class="mt-10">
                {{ $projects->links() }}
            </div>
        @endif
    </section>
</x-layouts.site>
