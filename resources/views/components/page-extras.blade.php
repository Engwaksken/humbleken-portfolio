@props(['page'])
{{-- Optional admin-managed text and image gallery shown below a page's main content. --}}
@if (filled($page['body']) || ! empty($page['gallery']))
    <section {{ $attributes->class('container-site section-bottom') }} aria-label="More about {{ $page['title'] }}">
        @if (filled($page['body']))
            <div class="max-w-3xl text-[1.0625rem] leading-8 whitespace-pre-line text-muted">{{ $page['body'] }}</div>
        @endif
        @if (! empty($page['gallery']))
            <ul @class(['grid gap-5 sm:grid-cols-2 lg:grid-cols-3', 'mt-8' => filled($page['body'])])>
                @foreach ($page['gallery'] as $item)
                    <li>
                        <figure class="card overflow-hidden">
                            <a href="{{ $item['image_url'] }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['caption'] }}" loading="lazy" class="aspect-[4/3] w-full object-cover transition hover:opacity-90">
                                <span class="sr-only">(opens full-size image in a new tab)</span>
                            </a>
                            @if (filled($item['caption']))
                                <figcaption class="p-4 text-sm leading-6 text-muted">{{ $item['caption'] }}</figcaption>
                            @endif
                        </figure>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endif
