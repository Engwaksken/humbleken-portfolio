@php
    $linkClass = 'focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
@endphp
<x-layouts.site :title="$article->title.' — '.$brandName" :description="$article->excerpt" og-type="article">
    <x-slot:head>
        <meta property="article:published_time" content="{{ $article->published_at->toAtomString() }}">
        <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
    </x-slot:head>

    <article aria-labelledby="article-title" class="mx-auto max-w-3xl px-6 py-20 lg:py-24">
        <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-brand hover:text-brand-dark {{ $linkClass }}">
            <span aria-hidden="true">←</span> All articles
        </a>

        <h1 id="article-title" class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl">{{ $article->title }}</h1>

        <p class="mt-5 text-sm text-[#506176]">
            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
            · {{ $article->readingMinutes() }} min read
            @if ($article->category)
                · <a href="{{ route('articles.index', ['category' => $article->category->slug]) }}" class="text-brand underline underline-offset-2 {{ $linkClass }}">{{ $article->category->name }}</a>
            @endif
        </p>

        @if ($article->excerpt)
            <p class="mt-6 text-lg leading-8 text-[#506176]">{{ $article->excerpt }}</p>
        @endif

        @if ($article->body)
            <div class="markdown-body mt-10 border-t border-[#14263d]/10 pt-10">
                {{ $article->bodyHtml() }}
            </div>
        @endif

        @if ($article->tags->isNotEmpty())
            <ul class="mt-10 flex flex-wrap gap-2" aria-label="Tags">
                @foreach ($article->tags as $tag)
                    <li>
                        <a href="{{ route('articles.index', ['tag' => $tag->slug]) }}" class="rounded bg-brand-tint px-2 py-1 text-xs font-medium hover:bg-brand-tint-strong {{ $linkClass }}">#{{ $tag->name }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-14 border-l-2 border-brand pl-4">
            <p class="font-semibold">Need help applying this?</p>
            <a href="{{ route('home') }}#contact" class="mt-2 inline-block text-sm font-semibold text-brand underline underline-offset-4 hover:text-brand-dark {{ $linkClass }}">Send an enquiry</a>
        </div>
    </article>
</x-layouts.site>
