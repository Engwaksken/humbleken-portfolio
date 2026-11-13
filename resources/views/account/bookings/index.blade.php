<x-layouts.site :title="'Your bookings — '.$brandName">
    <div class="mx-auto w-full max-w-4xl section px-4 sm:px-6">
        <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> Your account
        </a>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Bookings and services</h1>
                <p class="mt-1 text-sm text-muted">Appointment requests made while signed in or with {{ auth()->user()->email }}.</p>
            </div>
            <a href="{{ route('booking') }}" class="btn-primary w-full sm:w-fit">Request an appointment</a>
        </div>

        <div class="mt-8">
            @if ($bookings->isEmpty())
                <p class="border-l-2 border-brand py-2 pl-4 text-muted">You have no appointment requests yet.</p>
            @else
                @include('account.partials.booking-list')
                <div class="mt-8">{{ $bookings->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.site>
