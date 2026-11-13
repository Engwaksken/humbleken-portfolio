@php
    $imageUrl = $article->featuredImageUrl();
    $position = $article->featuredImagePosition();
    $heroPosition = $imageUrl ? $position->heroPosition() : null;
    $imageBelow = $imageUrl && $heroPosition === null;
    $articleUrl = route('articles.show', ['article' => $article->slug]);
    $wasUpdated = $article->updated_at && $article->updated_at->gt($article->published_at->copy()->addDay());
    $initials = collect(preg_split('/\s+/', trim($owner['name'])))->filter()->take(2)->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $share = [
        ['label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in', 'url' => 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($articleUrl)],
        ['label' => 'X', 'icon' => 'fa-brands fa-x-twitter', 'url' => 'https://twitter.com/intent/tweet?url='.urlencode($articleUrl).'&text='.urlencode($article->title)],
        ['label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'url' => 'https://wa.me/?text='.urlencode($article->title.' '.$articleUrl)],
        ['label' => 'Email', 'icon' => 'fa-solid fa-envelope', 'url' => 'mailto:?subject='.rawurlencode($article->title).'&body='.rawurlencode($articleUrl)],
    ];
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $article->title,
        'description' => $article->excerpt,
        'image' => $imageUrl,
        'datePublished' => $article->published_at->toAtomString(),
        'dateModified' => $article->updated_at?->toAtomString(),
        'author' => ['@type' => 'Person', 'name' => $owner['name'], 'url' => route('home')],
        'mainEntityOfPage' => $articleUrl,
        'keywords' => $article->tags->pluck('name')->implode(', ') ?: null,
    ]);
    $avatarClass = 'author-avatar grid shrink-0 place-items-center overflow-hidden rounded-full bg-brand-tint font-semibold text-brand-dark';
@endphp
<x-layouts.site :title="$article->title.' | '.$brandName" :description="$article->excerpt" og-type="article" :og-image="$imageUrl">
    <x-slot:head>
        <meta property="article:published_time" content="{{ $article->published_at->toAtomString() }}">
        <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
        <meta property="article:author" content="{{ $owner['name'] }}">
        @foreach ($article->tags as $tag)
            <meta property="article:tag" content="{{ $tag->name }}">
        @endforeach
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    </x-slot:head>

    {{-- Reading progress, filled in by resources/js/article.js. --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[70] h-1" aria-hidden="true">
        <div class="h-full origin-left scale-x-0 bg-brand transition-transform duration-150 ease-out" data-reading-progress></div>
    </div>

    <x-page-hero :eyebrow="$article->category?->name" :title="$article->title" :intro="$article->excerpt" :intro-lines="0" title-id="article-title"
        :narrow="! in_array($heroPosition, ['right', 'left'], true)" :image="$heroPosition ? $imageUrl : null" :image-alt="(string) $article->featured_image_alt" :image-position="$heroPosition" full>
        <x-slot:before>
            <nav aria-label="Breadcrumb" class="breadcrumb mb-8">
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted">
                    <li><a href="{{ route('home') }}" class="font-medium hover:text-brand-dark">Home</a></li>
                    <li aria-hidden="true"><i class="fa-solid fa-chevron-right text-[0.625rem]"></i></li>
                    <li><a href="{{ route('articles.index') }}" class="font-medium hover:text-brand-dark">Articles</a></li>
                    @if ($article->category)
                        <li aria-hidden="true"><i class="fa-solid fa-chevron-right text-[0.625rem]"></i></li>
                        <li><a href="{{ route('articles.index', ['category' => $article->category->slug]) }}" class="font-medium hover:text-brand-dark">{{ $article->category->name }}</a></li>
                    @endif
                </ol>
            </nav>
        </x-slot:before>

        <div class="mt-8 flex items-center gap-4 border-t border-line/60 pt-6">
            @if ($owner['photo_url'])
                <img src="{{ $owner['photo_url'] }}" alt="" class="{{ $avatarClass }} size-12 object-cover">
            @else
                <span class="{{ $avatarClass }} size-12 text-sm" aria-hidden="true">{{ $initials }}</span>
            @endif
            <div class="min-w-0 text-sm leading-6">
                <p class="font-semibold text-ink">{{ $owner['name'] }}@if (filled($owner['role']))<span class="font-normal text-muted"> · {{ $owner['role'] }}</span>@endif</p>
                <p class="text-muted">
                    <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
                    <span aria-hidden="true"> · </span>{{ $article->readingMinutes() }} min read
                    @if ($wasUpdated)
                        <span aria-hidden="true"> · </span>Updated <time datetime="{{ $article->updated_at->toDateString() }}">{{ $article->updated_at->format('j F Y') }}</time>
                    @endif
                </p>
            </div>
        </div>
    </x-page-hero>

    @if ($imageBelow)
        <figure class="container-site mt-8 sm:mt-12">
            <img src="{{ $imageUrl }}" alt="{{ $article->featured_image_alt }}" fetchpriority="high" decoding="async" class="mx-auto aspect-[16/9] w-full max-w-5xl rounded-2xl border border-line object-cover">
        </figure>
    @endif

    <div class="container-site section">
        <div class="grid gap-10 xl:grid-cols-[minmax(0,1fr)_minmax(0,46rem)_minmax(0,1fr)]">
            {{-- Share links: a sticky column on wide screens. --}}
            <aside class="hidden xl:block" aria-label="Share this article">
                <div class="sticky top-28 ml-auto flex w-fit flex-col items-center gap-2">
                    <p class="mb-1 text-[0.6875rem] font-bold tracking-[.14em] text-muted uppercase">Share</p>
                    @include('articles.partials.share', ['vertical' => true])
                </div>
            </aside>

            <article aria-labelledby="article-title" class="min-w-0" data-article-body>
                @if (count($contents['toc']) >= 2)
                    <details class="mb-10 rounded-2xl border border-line bg-white p-5 xl:hidden">
                        <summary class="cursor-pointer text-sm font-semibold">On this page</summary>
                        @include('articles.partials.toc')
                    </details>
                @endif

                @if ($article->body)
                    <div class="markdown-body article-body">{{ $contents['html'] }}</div>
                @endif

                @if ($article->tags->isNotEmpty())
                    <ul class="mt-12 flex flex-wrap gap-2" aria-label="Tags">
                        @foreach ($article->tags as $tag)
                            <li>
                                <a href="{{ route('articles.index', ['tag' => $tag->slug]) }}" class="chip bg-secondary-tint text-ink hover:bg-brand hover:text-white">#{{ $tag->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-10 flex flex-col gap-3 border-y border-line py-5 sm:flex-row sm:items-center sm:justify-between xl:hidden">
                    <p class="text-sm font-semibold">Share this article</p>
                    @include('articles.partials.share', ['vertical' => false])
                </div>

                {{-- About the author --}}
                <section aria-labelledby="author-title" class="mt-12 rounded-2xl border border-line bg-white p-6 sm:p-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        @if ($owner['photo_url'])
                            <img src="{{ $owner['photo_url'] }}" alt="" loading="lazy" class="{{ $avatarClass }} size-20 object-cover">
                        @else
                            <span class="{{ $avatarClass }} size-20 text-xl" aria-hidden="true">{{ $initials }}</span>
                        @endif
                        <div class="min-w-0">
                            <p class="text-xs font-bold tracking-[.14em] text-muted uppercase">About the author</p>
                            <h2 id="author-title" class="mt-1 text-xl font-semibold">{{ $owner['name'] }}</h2>
                            @if (filled($owner['role']))
                                <p class="text-sm text-muted">{{ $owner['role'] }}</p>
                            @endif
                            <p class="mt-3 leading-7 text-muted">{{ filled($owner['bio']) ? $owner['bio'] : 'I help people and organisations solve technical problems, improve their systems and make confident technology decisions. If this article raised a question about your own setup, I am happy to help.' }}</p>
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ route('contact') }}" class="btn-primary">Get in touch <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                                <a href="{{ route('booking') }}" class="btn-ghost">Book a consultation</a>
                            </div>
                        </div>
                    </div>
                </section>
            </article>

            {{-- Table of contents: a sticky column on wide screens. --}}
            <aside class="hidden xl:block" @if (count($contents['toc']) >= 2) aria-label="On this page" @else aria-hidden="true" @endif>
                @if (count($contents['toc']) >= 2)
                    <div class="sticky top-28 max-w-60">
                        <p class="text-[0.6875rem] font-bold tracking-[.14em] text-muted uppercase">On this page</p>
                        @include('articles.partials.toc')
                    </div>
                @endif
            </aside>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related-title" class="border-t border-line bg-white section">
            <div class="container-site">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="eyebrow">Keep reading</p>
                        <h2 id="related-title" class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Related articles</h2>
                    </div>
                    <a href="{{ route('articles.index') }}" class="link-arrow">All articles <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </div>
                <div class="section-body grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $relatedArticle)
                        @include('articles.partials.card', ['article' => $relatedArticle, 'headingTag' => 'h3'])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
