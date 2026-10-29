<ul class="divide-y divide-[#14263d]/10 border-y border-[#14263d]/10 bg-white">
    @foreach ($tickets as $ticket)
        <li class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('account.tickets.show', $ticket) }}" class="font-semibold hover:text-[#077784] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">
                    #{{ $ticket->id }} {{ $ticket->subject }}
                </a>
                <p class="text-sm text-[#506176]">
                    Last activity <time datetime="{{ $ticket->last_activity_at?->toAtomString() }}">{{ $ticket->last_activity_at?->diffForHumans() }}</time>
                </p>
            </div>
            @include('account.partials.status-badge', ['status' => $ticket->status])
        </li>
    @endforeach
</ul>
