@php
    $badgeClass = match ($status) {
        \App\Enums\TicketStatus::Open => 'bg-[#fff4e5] text-[#7a4100]',
        \App\Enums\TicketStatus::Answered => 'bg-brand-tint text-brand-dark',
        \App\Enums\TicketStatus::Closed => 'bg-[#eef0f3] text-[#3d4b5c]',
    };
    $badgeLabel = match ($status) {
        \App\Enums\TicketStatus::Open => 'Waiting for us',
        \App\Enums\TicketStatus::Answered => 'Waiting for you',
        \App\Enums\TicketStatus::Closed => 'Closed',
    };
@endphp
<span class="inline-block rounded px-2 py-1 text-xs font-semibold {{ $badgeClass }}">{{ $badgeLabel }}</span>
