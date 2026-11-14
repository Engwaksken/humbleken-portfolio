<x-layouts.site :title="'Privacy policy | '.$brandName" description="How {{ $brandName }} collects, uses and protects your personal information on this website and in the mobile app.">
    <x-page-hero eyebrow="Privacy" title="Privacy policy." :intro="$updatedAt ? 'Last updated '.\Illuminate\Support\Carbon::parse($updatedAt)->format('j F Y').'.' : null" />

    <section class="container-site section">
        <article class="article-body mx-auto max-w-3xl">
            {{ $policy }}
        </article>
    </section>
</x-layouts.site>
