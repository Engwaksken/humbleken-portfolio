<x-layouts.site :title="'Your support tickets — '.$brandName">
    <div class="mx-auto w-full max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
        <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> Your account
        </a>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <h1 class="text-3xl font-semibold tracking-tight">Support tickets</h1>
            <a href="{{ route('account.tickets.create') }}" class="btn-primary w-full sm:w-fit">Open a ticket</a>
        </div>

        <div class="mt-8">
            @if ($tickets->isEmpty())
                <p class="border-l-2 border-brand py-2 pl-4 text-muted">You have no support tickets yet.</p>
            @else
                @include('account.partials.ticket-list')
                <div class="mt-8">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.site>
