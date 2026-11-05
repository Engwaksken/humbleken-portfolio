@php
    $chatEnabled = \App\Support\AiChat::enabled();
    $suggestions = ['What services do you offer?', 'How does a consultation work?', 'Can you help with a network issue?'];
    $botAvatar = '<svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><rect x="4" y="7" width="16" height="12" rx="4"/><path d="M12 3v4M9 12.5h.01M15 12.5h.01M9.5 16h5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"/></svg>';
@endphp
<div data-a11y-exempt data-chatbot data-endpoint="{{ route('chatbot.messages') }}" data-enabled="{{ $chatEnabled ? 'true' : 'false' }}">
    <button type="button" aria-expanded="false" aria-controls="chatbot-panel" aria-haspopup="dialog" title="Chat with us (Alt + C)"
        class="group fixed right-4 bottom-4 z-50 inline-flex size-14 items-center justify-center gap-2 rounded-full bg-brand font-semibold text-white shadow-[0_10px_30px_-8px_rgb(7_119_132/0.7)] ring-4 ring-white transition hover:bg-brand-dark sm:right-6 sm:bottom-6 sm:size-auto sm:min-h-14 sm:px-6 aria-expanded:opacity-0 aria-expanded:pointer-events-none max-sm:aria-expanded:scale-75" data-chatbot-toggle>
        <span class="relative" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="size-6 fill-none stroke-current" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5 8 8 0 0 1-3.5-.8L4 20l1.3-4.2a7.5 7.5 0 1 1 14.7-4.3Z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.5 11.5h.01M12.5 11.5h.01M16.5 11.5h.01" stroke-linecap="round" stroke-width="2.5"/></svg>
            @if ($chatEnabled)
                <span class="absolute -top-0.5 -right-0.5 size-2.5 rounded-full bg-[#4ade80] ring-2 ring-brand"></span>
            @endif
        </span>
        <span class="max-sm:sr-only">Chat with us</span>
    </button>

    <section id="chatbot-panel" role="dialog" aria-labelledby="chatbot-title" aria-modal="false" hidden
        class="fixed inset-0 z-[66] flex flex-col overflow-hidden bg-white text-ink animate-fade-up sm:inset-auto sm:right-6 sm:bottom-6 sm:h-[min(40rem,calc(100dvh-3rem))] sm:w-[25rem] sm:rounded-3xl sm:border sm:border-line sm:shadow-[0_24px_60px_-16px_rgb(20_38_61/0.45)]">
        <header class="relative flex items-center gap-3 bg-[linear-gradient(135deg,var(--color-secondary),var(--color-brand-dark))] px-4 pt-[max(1rem,env(safe-area-inset-top))] pb-4 text-white">
            <span class="relative grid size-11 shrink-0 place-items-center rounded-2xl bg-white/15" aria-hidden="true">
                {!! $botAvatar !!}
                <span class="absolute -right-0.5 -bottom-0.5 size-3 rounded-full ring-2 ring-secondary {{ $chatEnabled ? 'bg-[#4ade80]' : 'bg-white/50' }}"></span>
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="chatbot-title" class="truncate font-semibold">Website assistant</h2>
                <p class="truncate text-xs text-white/75">{{ $chatEnabled ? 'Online · Replies in a few seconds' : 'Offline · Contact us directly' }}</p>
            </div>
            @if ($chatEnabled)
                <button type="button" class="grid size-10 place-items-center rounded-full text-white/85 hover:bg-white/15 hover:text-white" aria-label="Start a new conversation" title="New conversation" data-chatbot-reset>
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.9"><path d="M4 12a8 8 0 1 0 2.34-5.66M4 4v5h5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            @endif
            <button type="button" class="grid size-10 place-items-center rounded-full text-white/85 hover:bg-white/15 hover:text-white" aria-label="Close assistant" title="Close" data-chatbot-close>
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
            </button>
        </header>

        <div class="chat-scroll flex-1 overflow-y-auto bg-canvas px-4 py-5" data-chatbot-scroll>
            <div class="space-y-4" role="log" aria-live="polite" aria-relevant="additions text" aria-label="Chat messages" data-chatbot-log>
                <div class="flex items-end gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-ink text-white" aria-hidden="true">{!! $botAvatar !!}</span>
                    <div class="max-w-[85%] rounded-2xl rounded-bl-md border border-line bg-white px-4 py-3 text-sm leading-6 shadow-sm">
                        @if ($chatEnabled)
                            <p class="font-semibold">Hi there 👋</p>
                            <p class="mt-1 text-muted">Ask me about our services, projects, or articles. You can also attach a file for me to review.</p>
                        @else
                            <p>Our assistant is being set up. Please contact us and we’ll be happy to help.</p>
                            <a href="{{ route('contact') }}" class="mt-2 inline-flex font-semibold text-brand hover:text-brand-dark">Go to the contact page →</a>
                        @endif
                    </div>
                </div>
            </div>
            @if ($chatEnabled)
                <div class="mt-4 flex flex-wrap gap-2 pl-10" data-chatbot-suggestions aria-label="Suggested questions" role="group">
                    @foreach ($suggestions as $suggestion)
                        <button type="button" class="rounded-full border border-brand/30 bg-white px-3.5 py-2 text-left text-xs font-medium text-brand-dark transition hover:border-brand hover:bg-brand-tint" data-chatbot-suggestion>{{ $suggestion }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        <form class="border-t border-line bg-white px-3 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]" data-chatbot-form data-enabled="{{ $chatEnabled ? 'true' : 'false' }}" enctype="multipart/form-data">
            <div class="mb-2 hidden items-center gap-2 rounded-xl bg-brand-tint px-3 py-2 text-xs text-brand-dark" data-chatbot-file>
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4 shrink-0 fill-none stroke-current" stroke-width="2"><path d="m21 11-8.5 8.5a5 5 0 0 1-7-7L14 4a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L15 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="min-w-0 flex-1 truncate font-medium" data-chatbot-file-name aria-live="polite"></span>
                <button type="button" class="grid size-7 place-items-center rounded-full hover:bg-white" aria-label="Remove attachment" data-chatbot-file-remove>
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4 fill-none stroke-current" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                </button>
            </div>
            <p class="mb-2 hidden rounded-xl bg-[#fef3f2] px-3 py-2 text-xs font-medium text-[#b42318]" role="alert" data-chatbot-error></p>

            <div class="flex items-end gap-2 rounded-2xl border border-ink/15 bg-white p-1.5 transition focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
                <label for="chatbot-attachment" class="grid size-10 shrink-0 cursor-pointer place-items-center rounded-xl text-muted hover:bg-brand-tint hover:text-brand-dark focus-within:outline-2 focus-within:outline-brand {{ $chatEnabled ? '' : 'pointer-events-none opacity-50' }}" title="Attach a PDF, image, or text file (maximum 10 MB)">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.9"><path d="m21 11-8.5 8.5a5 5 0 0 1-7-7L14 4a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L15 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="sr-only">Attach a file</span>
                </label>
                <input id="chatbot-attachment" name="attachment" type="file" accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.txt,.csv,application/pdf,image/png,image/jpeg,image/webp,image/gif,text/plain,text/csv" class="sr-only" aria-describedby="chatbot-attachment-help" @disabled(! $chatEnabled)>
                <label class="sr-only" for="chatbot-message">Your message</label>
                <textarea id="chatbot-message" name="message" rows="1" maxlength="2000" autocomplete="off" enterkeyhint="send"
                    class="max-h-32 min-h-10 min-w-0 flex-1 resize-none bg-transparent px-1 py-2 text-base leading-6 placeholder:text-muted/70 focus:outline-none sm:text-sm"
                    placeholder="{{ $chatEnabled ? 'Type your question…' : 'Assistant is not configured' }}" aria-describedby="chatbot-message-help" @disabled(! $chatEnabled)></textarea>
                <button type="submit" class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand text-white transition hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-40" aria-label="Send message" @disabled(! $chatEnabled)>
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <p id="chatbot-message-help" class="sr-only">Press Enter to send and Shift + Enter for a new line.</p>
            <p id="chatbot-attachment-help" class="mt-2 px-1 text-[0.6875rem] leading-4 text-muted">
                @if ($chatEnabled)
                    PDF, image, TXT, or CSV up to 10 MB; processed for this chat only. AI-generated answers may be inaccurate. Don’t share sensitive information.
                @else
                    Chat is unavailable until an administrator configures and enables an AI provider.
                @endif
            </p>
        </form>
    </section>
</div>
