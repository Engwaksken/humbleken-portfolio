<x-layouts.site :title="'Projects — '.$brandName" description="Selected projects and case studies.">
    <x-page-hero eyebrow="Projects" title="Selected work and case studies." title-id="projects-title"
        intro="A selection of engagements showing the problem, the approach taken, and the outcome delivered." />

    <section aria-labelledby="projects-title" class="container-site section">
        @if ($projects->isEmpty())
            <p class="card p-8 text-center text-muted">Projects are being added.</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    @include('projects.partials.card')
                @endforeach
            </div>

            <div class="mt-8 sm:mt-10">
                {{ $projects->links() }}
            </div>
        @endif
    </section>

    <x-cta-band title="Working on something similar?" />
</x-layouts.site>
