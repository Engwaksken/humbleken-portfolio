@props(['avatar' => null])
@php
    $chatEnabled = \App\Support\AiChat::enabled();
    $suggestions = ['What services do you offer?', 'How does a consultation work?', 'Can you help with a network issue?'];
@endphp
<div data-a11y-exempt data-chatbot data-endpoint="{{ route('chatbot.messages') }}" data-enabled="{{ $chatEnabled ? 'true' : 'false' }}" @if ($avatar) data-avatar="{{ $avatar }}" @endif>
    <button type="button" aria-expanded="false" aria-controls="chatbot-panel" aria-haspopup="dialog" title="Ask a question (Alt + C)"
        class="group fixed right-3 bottom-3 z-50 inline-flex size-12 items-center justify-center gap-2.5 rounded-full bg-brand font-semibold text-white shadow-[0_10px_30px_-8px_rgb(7_119_132/0.7)] ring-2 ring-white sm:ring-4 transition hover:bg-brand-dark sm:right-6 sm:bottom-6 sm:size-auto sm:min-h-14 sm:pr-6 sm:pl-2 aria-expanded:pointer-events-none aria-expanded:opacity-0 max-sm:aria-expanded:scale-75" data-chatbot-toggle>
        <span class="relative grid size-9 shrink-0 sm:size-10 place-items-center overflow-hidden rounded-full bg-white" aria-hidden="true">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="" class="size-full object-cover">
            @else
                <i class="fa-solid fa-comment-dots text-lg text-brand"></i>
            @endif
        </span>
        <span class="max-sm:sr-only">Ask a question</span>
        @if ($chatEnabled)
            <span class="absolute top-0.5 right-0.5 size-3 rounded-full bg-[#4ade80] ring-2 ring-white" aria-hidden="true"></span>
        @endif
    </button>

    <section id="chatbot-panel" role="dialog" aria-labelledby="chatbot-title" aria-modal="false" hidden
        class="fixed inset-0 z-[66] flex flex-col overflow-hidden bg-white text-ink animate-fade-up sm:inset-auto sm:right-6 sm:bottom-6 sm:h-[min(40rem,calc(100dvh-3rem))] sm:w-[25rem] sm:rounded-3xl sm:border sm:border-line sm:shadow-[0_24px_60px_-16px_rgb(20_38_61/0.45)]">
        <header class="flex items-center gap-3 bg-secondary px-4 pt-[max(1rem,env(safe-area-inset-top))] pb-4 text-white">
            <span class="relative shrink-0" aria-hidden="true">
                <span class="grid size-11 place-items-center overflow-hidden rounded-full bg-white">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="" class="size-full object-cover">
                    @else
                        <i class="fa-solid fa-robot text-lg text-secondary"></i>
                    @endif
                </span>
                <span class="absolute -right-0.5 -bottom-0.5 size-3 rounded-full ring-2 ring-secondary {{ $chatEnabled ? 'bg-[#4ade80]' : 'bg-white/50' }}"></span>
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="chatbot-title" class="truncate font-semibold">Website assistant</h2>
                <p class="truncate text-xs text-white/75">{{ $chatEnabled ? 'Online · Replies in a few seconds' : 'Offline · Contact me directly' }}</p>
            </div>
            @if ($chatEnabled)
                <button type="button" class="grid size-10 place-items-center rounded-full text-white/85 hover:bg-white/15 hover:text-white" aria-label="Start a new conversation" title="New conversation" data-chatbot-reset>
                    <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
                </button>
            @endif
            <button type="button" class="grid size-10 place-items-center rounded-full text-white/85 hover:bg-white/15 hover:text-white" aria-label="Close assistant" title="Close" data-chatbot-close>
                <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
            </button>
        </header>

        <div class="chat-scroll flex-1 overflow-y-auto bg-canvas px-4 py-5" data-chatbot-scroll>
            <div class="space-y-4" role="log" aria-live="polite" aria-relevant="additions text" aria-label="Chat messages" data-chatbot-log>
                <div class="flex items-end gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center overflow-hidden rounded-full border border-line bg-white" aria-hidden="true">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="" class="size-full object-cover">
                        @else
                            <i class="fa-solid fa-robot text-sm text-secondary"></i>
                        @endif
                    </span>
                    <div class="max-w-[85%] rounded-2xl rounded-bl-md border border-line bg-white px-4 py-3 text-sm leading-6 shadow-sm">
                        @if ($chatEnabled)
                            <p class="font-semibold">Hi there</p>
                            <p class="mt-1 text-muted">Ask me about the services, projects or articles on this site. You can also attach a file for me to review.</p>
                        @else
                            <p>The assistant is being set up. Please contact me directly and I will be happy to help.</p>
                            <a href="{{ route('contact') }}" class="mt-2 inline-flex items-center gap-1.5 font-semibold text-brand hover:text-brand-dark">Go to the contact page <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
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
                <i class="fa-solid fa-paperclip shrink-0" aria-hidden="true"></i>
                <span class="min-w-0 flex-1 truncate font-medium" data-chatbot-file-name aria-live="polite"></span>
                <button type="button" class="grid size-7 place-items-center rounded-full hover:bg-white" aria-label="Remove attachment" data-chatbot-file-remove>
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <p class="mb-2 hidden rounded-xl bg-[#fef3f2] px-3 py-2 text-xs font-medium text-[#b42318]" role="alert" data-chatbot-error></p>

            <div class="flex items-end gap-2 rounded-2xl border border-ink/15 bg-white p-1.5 transition focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
                <label for="chatbot-attachment" class="grid size-10 shrink-0 cursor-pointer place-items-center rounded-xl text-muted hover:bg-brand-tint hover:text-brand-dark focus-within:outline-2 focus-within:outline-brand {{ $chatEnabled ? '' : 'pointer-events-none opacity-50' }}" title="Attach a PDF, image, or text file (maximum 10 MB)">
                    <i class="fa-solid fa-paperclip text-lg" aria-hidden="true"></i>
                    <span class="sr-only">Attach a file</span>
                </label>
                <input id="chatbot-attachment" name="attachment" type="file" accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.txt,.csv,application/pdf,image/png,image/jpeg,image/webp,image/gif,text/plain,text/csv" class="sr-only" aria-describedby="chatbot-attachment-help" @disabled(! $chatEnabled)>
                <label class="sr-only" for="chatbot-message">Your message</label>
                <textarea id="chatbot-message" name="message" rows="1" maxlength="2000" autocomplete="off" enterkeyhint="send"
                    class="max-h-32 min-h-10 min-w-0 flex-1 resize-none bg-transparent px-1 py-2 text-base leading-6 placeholder:text-muted/70 focus:outline-none sm:text-sm"
                    placeholder="{{ $chatEnabled ? 'Type your question…' : 'Assistant is not configured' }}" aria-describedby="chatbot-message-help" @disabled(! $chatEnabled)></textarea>
                <button type="submit" class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand text-white transition hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-40" aria-label="Send message" @disabled(! $chatEnabled)>
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
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
