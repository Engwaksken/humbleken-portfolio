{{-- Share links (plain links, no third-party scripts) and a copy-link button. Expects $share, $articleUrl, $vertical. --}}
@php($button = 'grid size-10 place-items-center rounded-full border border-line bg-white text-ink transition hover:border-brand hover:text-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand')
<ul @class(['flex gap-2', 'flex-col' => $vertical, 'flex-wrap' => ! $vertical])>
    @foreach ($share as $link)
        <li>
            <a href="{{ $link['url'] }}" class="{{ $button }}" target="_blank" rel="noopener noreferrer" aria-label="Share on {{ $link['label'] }}" title="Share on {{ $link['label'] }}">
                <i class="{{ $link['icon'] }} text-sm" aria-hidden="true"></i>
            </a>
        </li>
    @endforeach
    <li>
        <button type="button" class="{{ $button }}" data-copy-link="{{ $articleUrl }}" aria-label="Copy link" title="Copy link">
            <i class="fa-solid fa-link text-sm" aria-hidden="true" data-copy-icon></i>
        </button>
    </li>
</ul>
