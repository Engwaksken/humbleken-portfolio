{{-- Article card. Expects $article; $headingTag sets the heading level (h2 on the list, h3 elsewhere). --}}
@php($headingTag = $headingTag ?? 'h2')
<article class="card-hover group relative flex flex-col overflow-hidden">
    @if ($article->featuredImageUrl())
        <div class="aspect-[16/9] overflow-hidden border-b border-line bg-canvas">
            <img src="{{ $article->featuredImageUrl() }}" alt="" loading="lazy" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        </div>
    @endif
    <div class="flex flex-1 flex-col p-6 sm:p-7">
        <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-muted">
            @if ($article->category)<span class="chip">{{ $article->category->name }}</span>@endif
            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
            <span aria-hidden="true">·</span>
            <span>{{ $article->readingMinutes() }} min read</span>
        </p>
        <{{ $headingTag }} class="mt-4 text-xl leading-snug font-semibold tracking-tight">
            <a href="{{ route('articles.show', ['article' => $article->slug]) }}" class="after:absolute after:inset-0 after:content-[''] group-hover:text-brand-dark">{{ $article->title }}</a>
        </{{ $headingTag }}>
        @if ($article->excerpt)
            <p class="mt-3 line-clamp-3 leading-7 text-muted">{{ $article->excerpt }}</p>
        @endif
        <span class="link-arrow mt-auto pt-6" aria-hidden="true">Read article <i class="fa-solid fa-arrow-right text-xs"></i></span>
    </div>
</article>
