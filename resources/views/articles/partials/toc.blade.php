<ol class="mt-3 space-y-1 border-l border-line text-sm">
    @foreach ($contents['toc'] as $item)
        <li>
            <a href="#{{ $item['id'] }}" data-toc-link="{{ $item['id'] }}"
                class="-ml-px block border-l-2 border-transparent py-1 pl-3 leading-6 text-muted transition hover:border-brand/50 hover:text-ink aria-[current=true]:border-brand aria-[current=true]:font-semibold aria-[current=true]:text-ink">{{ $item['text'] }}</a>
        </li>
    @endforeach
</ol>
