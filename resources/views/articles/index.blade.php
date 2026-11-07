@php
    $chip = 'inline-flex min-h-10 items-center rounded-full border px-4 text-sm font-medium transition';
    $chipOff = 'border-line bg-white text-ink hover:border-brand hover:text-brand-dark';
    $chipOn = 'border-ink bg-ink text-white';
    $filtered = $search !== '' || $activeCategory || $activeTag;
@endphp
<x-layouts.site :title="'Articles — '.$brandName" description="Practical articles on IT systems, troubleshooting and technology decisions.">
    <x-page-hero eyebrow="Articles" title="Notes on systems and decisions." title-id="articles-title"
        intro="Practical articles on IT systems, troubleshooting and technology decisions.">
        <form method="GET" action="{{ route('articles.index') }}" role="search" class="mt-6 flex max-w-xl flex-col gap-3 sm:flex-row">
            @if ($activeCategory)
                <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
            @endif
            @if ($activeTag)
                <input type="hidden" name="tag" value="{{ $activeTag->slug }}">
            @endif
            <label for="article-search" class="sr-only">Search articles</label>
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted" aria-hidden="true"></i>
                <input id="article-search" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Search by keyword" class="input !rounded-full pl-11">
            </div>
            <button type="submit" class="btn-primary">Search</button>
        </form>
    </x-page-hero>

    <section aria-labelledby="articles-title" class="container-site section">
        @if ($categories->isNotEmpty())
            <nav aria-label="Article categories" class="-mx-4 overflow-x-auto px-4 pb-2">
                <ul class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                    <li>
                        <a href="{{ route('articles.index', array_filter(['q' => $search ?: null, 'tag' => $activeTag?->slug])) }}"
                            class="{{ $chip }} {{ $activeCategory ? $chipOff : $chipOn }}" @if (! $activeCategory) aria-current="page" @endif>All</a>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('articles.index', array_filter(['q' => $search ?: null, 'tag' => $activeTag?->slug, 'category' => $category->slug])) }}"
                                class="{{ $chip }} {{ $activeCategory?->is($category) ? $chipOn : $chipOff }}" @if ($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        @if ($filtered)
            <p class="mt-6 text-sm text-muted">
                {{ trans_choice('{0} No articles|{1} 1 article|[2,*] :count articles', $articles->total()) }}
                @if ($search !== '') matching “{{ $search }}”@endif
                @if ($activeTag) tagged “{{ $activeTag->name }}”@endif
                @if ($activeCategory) in {{ $activeCategory->name }}@endif.
                <a href="{{ route('articles.index') }}" class="ml-1 font-semibold text-brand hover:text-brand-dark">Clear filters</a>
            </p>
        @endif

        @if ($articles->isEmpty())
            <p class="card section-body p-6 text-center sm:p-8 text-muted">
                {{ $filtered ? 'Try a different search term or category.' : 'Articles are being added.' }}
            </p>
        @else
            <div class="section-body grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <article class="card-hover group relative flex flex-col p-6 sm:p-7">
                        <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-muted">
                            @if ($article->category)<span class="chip">{{ $article->category->name }}</span>@endif
                            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
                        </p>
                        <h2 class="mt-4 text-xl leading-snug font-semibold tracking-tight">
                            <a href="{{ route('articles.show', ['article' => $article->slug]) }}" class="after:absolute after:inset-0 after:content-[''] group-hover:text-brand-dark">{{ $article->title }}</a>
                        </h2>
                        @if ($article->excerpt)
                            <p class="mt-3 line-clamp-3 leading-7 text-muted">{{ $article->excerpt }}</p>
                        @endif
                        <span class="link-arrow mt-auto pt-6" aria-hidden="true">Read article <i class="fa-solid fa-arrow-right text-xs"></i></span>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 sm:mt-10">
                {{ $articles->links() }}
            </div>
        @endif
    </section>
</x-layouts.site>
