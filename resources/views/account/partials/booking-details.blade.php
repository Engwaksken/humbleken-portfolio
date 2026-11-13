{{-- One booking request as the customer sees it. Expects $booking. --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <p class="text-sm text-muted">Reference <span class="font-mono font-semibold text-ink">{{ $booking->reference }}</span></p>
        <p class="mt-1 font-semibold">{{ $booking->service?->name ?? 'General consultation' }}</p>
        <p class="text-sm text-muted">
            Preferred time <time datetime="{{ $booking->preferred_at->toAtomString() }}">{{ $booking->preferred_at->format('j M Y, H:i') }}</time>
            · Requested <time datetime="{{ $booking->created_at->toAtomString() }}">{{ $booking->created_at->diffForHumans() }}</time>
        </p>
    </div>
    @include('account.partials.booking-status-badge', ['status' => $booking->status])
</div>
@if (filled($booking->customer_note))
    <p class="mt-3 border-l-2 border-brand bg-canvas px-3 py-2 text-sm leading-6"><span class="font-semibold">Update from {{ $owner['name'] ?? 'me' }}:</span> {{ $booking->customer_note }}</p>
@endif
