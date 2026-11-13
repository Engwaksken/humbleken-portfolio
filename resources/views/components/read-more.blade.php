{{--
    Text shortened to $lines lines with a "Read more" toggle (see resources/js/read-more.js).
    The full text shows until the script confirms it overflows, so short text gets no button
    and visitors without JavaScript can still read everything. $lines = 0 always shows it all.
--}}
@props(['lines' => \App\Support\PageContent::INTRO_LINES, 'textClass' => ''])
@php
    $lines = \App\Support\PageContent::introLines($lines);
    $textId = 'read-more-'.\Illuminate\Support\Str::random(8);
@endphp
<div {{ $attributes }} @if ($lines > 0) data-read-more style="--read-more-lines: {{ $lines }}" @endif>
    <p id="{{ $textId }}" data-read-more-text class="{{ $textClass }}">{{ $slot }}</p>
    @if ($lines > 0)
        <button type="button" class="read-more-toggle" data-read-more-toggle aria-controls="{{ $textId }}" aria-expanded="false" hidden>
            <span data-read-more-label>Read more</span>
            <i class="fa-solid fa-chevron-down text-xs transition-transform" aria-hidden="true"></i>
        </button>
    @endif
</div>
