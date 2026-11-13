@php
    $badgeClass = match ($status) {
        \App\Enums\BookingStatus::New => 'bg-[#fff4e5] text-[#7a4100]',
        \App\Enums\BookingStatus::Contacted => 'bg-brand-tint text-brand-dark',
        \App\Enums\BookingStatus::Confirmed => 'bg-[#e7f6ec] text-[#13622f]',
        \App\Enums\BookingStatus::Completed => 'bg-[#eef0f3] text-[#3d4b5c]',
        \App\Enums\BookingStatus::Declined => 'bg-[#fef3f2] text-[#b42318]',
    };
@endphp
<span class="inline-block shrink-0 rounded px-2 py-1 text-xs font-semibold {{ $badgeClass }}">{{ $status->customerLabel() }}</span>
