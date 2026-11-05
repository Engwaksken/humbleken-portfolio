<x-layouts.site :title="$article->title.' — '.$brandName" :description="$article->excerpt" og-type="article">
    <x-slot:head>
        <meta property="article:published_time" content="{{ $article->published_at->toAtomString() }}">
        <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
    </x-slot:head>

    <x-page-hero :title="$article->title" :intro="$article->excerpt" title-id="article-title" narrow>
        <x-slot:before>
            <a href="{{ route('articles.index') }}" class="link-arrow mb-8 flex w-fit"><span aria-hidden="true">←</span> All articles</a>
        </x-slot:before>
        <p class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-muted">
            @if ($article->category)
                <a href="{{ route('articles.index', ['category' => $article->category->slug]) }}" class="chip hover:bg-brand hover:text-white">{{ $article->category->name }}</a>
            @endif
            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
            <span aria-hidden="true">·</span>
            <span>{{ $article->readingMinutes() }} min read</span>
        </p>
    </x-page-hero>

    <article aria-labelledby="article-title" class="container-site py-12 sm:py-16">
        <div class="mx-auto max-w-3xl">
            @if ($article->body)
                <div class="markdown-body">
                    {{ $article->bodyHtml() }}
                </div>
            @endif

            @if ($article->tags->isNotEmpty())
                <ul class="mt-12 flex flex-wrap gap-2 border-t border-line pt-8" aria-label="Tags">
                    @foreach ($article->tags as $tag)
                        <li>
                            <a href="{{ route('articles.index', ['tag' => $tag->slug]) }}" class="chip bg-secondary-tint text-ink hover:bg-brand hover:text-white">#{{ $tag->name }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-12 flex flex-col gap-5 rounded-2xl bg-secondary p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <div>
                    <p class="font-semibold">Need help applying this?</p>
                    <p class="mt-1 text-sm text-white/75">Talk to us about your own systems.</p>
                </div>
                <a href="{{ route('contact') }}" class="btn-light shrink-0">Send an enquiry</a>
            </div>
        </div>
    </article>
</x-layouts.site>
