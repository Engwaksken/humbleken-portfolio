@php
    $linkClass = 'focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
    $chip = 'rounded px-3 py-1 text-sm font-medium '.$linkClass;
    $filtered = $search !== '' || $activeCategory || $activeTag;
@endphp
<x-layouts.site :title="'Articles — '.$brandName" description="Practical articles on IT systems, troubleshooting and technology decisions.">
    <section aria-labelledby="articles-title" class="mx-auto max-w-4xl px-6 py-20 lg:py-24">
        <p class="text-sm font-semibold uppercase tracking-[.16em] text-brand">Articles</p>
        <h1 id="articles-title" class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">Notes on systems and decisions.</h1>

        <form method="GET" action="{{ route('articles.index') }}" role="search" class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-end">
            @if ($activeCategory)
                <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
            @endif
            @if ($activeTag)
                <input type="hidden" name="tag" value="{{ $activeTag->slug }}">
            @endif
            <div class="grid flex-1 gap-2">
                <label for="article-search" class="text-sm font-semibold">Search articles</label>
                <input id="article-search" name="q" type="search" value="{{ $search }}" maxlength="100"
                    class="rounded border border-[#14263d]/25 bg-white px-3 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
            </div>
            <button type="submit" class="rounded bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">Search</button>
        </form>

        @if ($categories->isNotEmpty())
            <nav aria-label="Article categories" class="mt-6">
                <ul class="flex flex-wrap gap-2">
                    <li>
                        <a href="{{ route('articles.index', array_filter(['q' => $search ?: null, 'tag' => $activeTag?->slug])) }}"
                            class="{{ $chip }} {{ $activeCategory ? 'bg-white' : 'bg-[#14263d] text-white' }}" @if (! $activeCategory) aria-current="page" @endif>All</a>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('articles.index', array_filter(['q' => $search ?: null, 'tag' => $activeTag?->slug, 'category' => $category->slug])) }}"
                                class="{{ $chip }} {{ $activeCategory?->is($category) ? 'bg-[#14263d] text-white' : 'bg-white' }}" @if ($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        @if ($filtered)
            <p class="mt-8 text-sm text-[#506176]">
                {{ trans_choice('{0} No articles|{1} 1 article|[2,*] :count articles', $articles->total()) }}
                @if ($search !== '') matching “{{ $search }}”@endif
                @if ($activeTag) tagged “{{ $activeTag->name }}”@endif
                @if ($activeCategory) in {{ $activeCategory->name }}@endif.
                <a href="{{ route('articles.index') }}" class="ml-1 font-semibold text-brand underline underline-offset-2 {{ $linkClass }}">Clear filters</a>
            </p>
        @endif

        @if ($articles->isEmpty())
            <p class="mt-10 border-l-2 border-brand py-2 pl-4 text-[#506176]">
                {{ $filtered ? 'Try a different search term or category.' : 'Articles are being added.' }}
            </p>
        @else
            <div class="mt-10 border-t border-[#14263d]/10">
                @foreach ($articles as $article)
                    <article class="border-b border-[#14263d]/10 py-8">
                        <p class="text-sm text-[#506176]">
                            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
                            @if ($article->category) · {{ $article->category->name }}@endif
                        </p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                            <a href="{{ route('articles.show', ['article' => $article->slug]) }}" class="hover:text-brand {{ $linkClass }}">{{ $article->title }}</a>
                        </h2>
                        @if ($article->excerpt)
                            <p class="mt-3 leading-7 text-[#506176]">{{ $article->excerpt }}</p>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $articles->links() }}
            </div>
        @endif
    </section>
</x-layouts.site>
