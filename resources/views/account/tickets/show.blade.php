<x-layouts.site :title="'Ticket #'.$ticket->id.' — '.$brandName">
    <div class="mx-auto w-full max-w-3xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
        <a href="{{ route('account.tickets.index') }}" class="text-sm font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> Support tickets
        </a>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <h1 class="text-3xl font-semibold tracking-tight">#{{ $ticket->id }} {{ $ticket->subject }}</h1>
            @include('account.partials.status-badge', ['status' => $ticket->status])
        </div>

        @if (session('status'))
            <p role="status" class="mt-6 border-l-2 border-brand bg-white px-4 py-3 text-sm font-medium">{{ session('status') }}</p>
        @endif

        <ol class="mt-10 grid gap-4" aria-label="Conversation">
            @foreach ($replies as $reply)
                <li class="p-5 {{ $reply->isFromStaff() ? 'border-l-4 border-brand bg-brand-tint' : 'bg-white' }}">
                    <p class="text-sm text-muted">
                        <span class="font-semibold text-ink">{{ $reply->isFromStaff() ? 'Support team' : 'You' }}</span>
                        · <time datetime="{{ $reply->created_at->toAtomString() }}">{{ $reply->created_at->format('j M Y, H:i') }}</time>
                    </p>
                    <div class="mt-3 leading-7 whitespace-pre-line">{{ $reply->body }}</div>
                    @if ($reply->attachments)
                        <ul class="mt-4 grid gap-2" aria-label="Attachments">
                            @foreach ($reply->attachments as $index => $attachment)
                                <li><a class="text-sm font-semibold font-semibold text-brand" href="{{ route('account.tickets.attachments.download', [$reply->id, $index]) }}">Download {{ $attachment['name'] }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($ticket->isClosed())
            <p class="mt-10 border-l-2 border-brand py-2 pl-4 text-muted">This ticket is closed. If you need more help, please <a href="{{ route('account.tickets.create') }}" class="font-semibold text-brand focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">open a new ticket</a>.</p>
        @else
            <form method="POST" action="{{ route('account.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="mt-10 grid gap-5 card p-6 sm:p-8" novalidate>
                @csrf
                <x-form.errors />
                <x-form.field name="body" label="Your reply" placeholder="Write your response" :rows="5" required />
                <div class="grid gap-2"><label for="reply-attachments" class="text-sm font-semibold">Attachments (optional, up to 5 files; 10 MB each)</label><input id="reply-attachments" name="attachments[]" type="file" multiple class="rounded border border-line px-3 py-2">@error('attachments.*')<p class="text-sm text-[#b42318]">{{ $message }}</p>@enderror</div>
                <x-button>Send reply</x-button>
            </form>
        @endif
    </div>
</x-layouts.site>
