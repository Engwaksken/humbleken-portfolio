<x-layouts.site :title="'Ticket #'.$ticket->id.' — '.$brandName">
    <div class="mx-auto max-w-3xl px-6 py-16 lg:py-20">
        <a href="{{ route('account.tickets.index') }}" class="text-sm font-semibold text-[#077784] hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">
            <span aria-hidden="true">←</span> Support tickets
        </a>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <h1 class="text-3xl font-semibold tracking-tight">#{{ $ticket->id }} {{ $ticket->subject }}</h1>
            @include('account.partials.status-badge', ['status' => $ticket->status])
        </div>

        @if (session('status'))
            <p role="status" class="mt-6 border-l-2 border-[#077784] bg-white px-4 py-3 text-sm font-medium">{{ session('status') }}</p>
        @endif

        <ol class="mt-10 grid gap-4" aria-label="Conversation">
            @foreach ($replies as $reply)
                <li class="p-5 {{ $reply->isFromStaff() ? 'border-l-4 border-[#077784] bg-[#e8f2f6]' : 'bg-white' }}">
                    <p class="text-sm text-[#506176]">
                        <span class="font-semibold text-[#14263d]">{{ $reply->isFromStaff() ? 'Support team' : 'You' }}</span>
                        · <time datetime="{{ $reply->created_at->toAtomString() }}">{{ $reply->created_at->format('j M Y, H:i') }}</time>
                    </p>
                    <div class="mt-3 leading-7 whitespace-pre-line">{{ $reply->body }}</div>
                </li>
            @endforeach
        </ol>

        @if ($ticket->isClosed())
            <p class="mt-10 border-l-2 border-[#077784] py-2 pl-4 text-[#506176]">This ticket is closed. If you need more help, please <a href="{{ route('account.tickets.create') }}" class="text-[#077784] underline underline-offset-2 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">open a new ticket</a>.</p>
        @else
            <form method="POST" action="{{ route('account.tickets.reply', $ticket) }}" class="mt-10 grid gap-5 bg-white p-6" novalidate>
                @csrf
                <x-form.errors />
                <x-form.field name="body" label="Your reply" :rows="5" required />
                <x-button>Send reply</x-button>
            </form>
        @endif
    </div>
</x-layouts.site>
