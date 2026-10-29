<x-layouts.site :title="'Your support tickets — '.$brandName">
    <div class="mx-auto max-w-4xl px-6 py-16 lg:py-20">
        <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-[#077784] hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">
            <span aria-hidden="true">←</span> Your account
        </a>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <h1 class="text-3xl font-semibold tracking-tight">Support tickets</h1>
            <a href="{{ route('account.tickets.create') }}" class="w-fit rounded bg-[#077784] px-4 py-2 text-sm font-semibold text-white hover:bg-[#075d68] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">Open a ticket</a>
        </div>

        <div class="mt-8">
            @if ($tickets->isEmpty())
                <p class="border-l-2 border-[#077784] py-2 pl-4 text-[#506176]">You have no support tickets yet.</p>
            @else
                @include('account.partials.ticket-list')
                <div class="mt-8">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.site>
