@php($chatEnabled = \App\Support\AiChat::enabled())
<div class="fixed bottom-4 right-4 z-50 sm:bottom-6 sm:right-6" data-chatbot data-endpoint="{{ route('chatbot.messages') }}">
        <button type="button" aria-expanded="false" aria-controls="chatbot-panel" class="inline-flex min-h-12 items-center gap-2 rounded-full bg-brand px-5 py-3 font-semibold text-white shadow-lg hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand" data-chatbot-toggle>
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5 8 8 0 0 1-3.5-.8L4 20l1.3-4.2a7.5 7.5 0 1 1 14.7-4.3Z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.5 11.5h.01M12.5 11.5h.01M16.5 11.5h.01" stroke-linecap="round" stroke-width="2.5"/></svg>
            <span>Chat with us</span>
        </button>
        <section id="chatbot-panel" aria-label="Website assistant" hidden class="absolute bottom-14 right-0 flex h-[min(70vh,34rem)] w-[min(90vw,23rem)] flex-col overflow-hidden rounded-xl border border-[#14263d]/20 bg-white text-[#14263d] shadow-2xl">
            <header class="flex items-center justify-between bg-[#14263d] px-4 py-3 text-white"><h2 class="font-semibold">Website assistant</h2><button type="button" aria-label="Close assistant" data-chatbot-close class="rounded px-2 text-xl">×</button></header>
            <div class="flex-1 space-y-3 overflow-y-auto p-4" aria-live="polite" aria-relevant="additions text" data-chatbot-log><p class="rounded bg-brand-tint p-3 text-sm">{{ $chatEnabled ? 'Ask me about our services, projects, or articles.' : 'Our assistant is being set up. Please contact us and we’ll be happy to help.' }}</p></div>
            @if ($chatEnabled)
                <form class="flex gap-2 border-t p-3" data-chatbot-form><label class="sr-only" for="chatbot-message">Your message</label><input id="chatbot-message" name="message" maxlength="2000" autocomplete="off" required class="min-w-0 flex-1 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand" placeholder="Type your question"><button type="submit" class="inline-flex items-center gap-1 rounded bg-brand px-3 py-2 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"><span>Send</span><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="2"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form>
                <p class="px-3 pb-2 text-xs text-[#506176]">AI-generated answers may be inaccurate. Don’t share sensitive information.</p>
            @else
                <a href="{{ route('contact') }}" class="m-3 inline-flex min-h-11 items-center justify-center rounded bg-brand px-4 py-2 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Contact us</a>
            @endif
        </section>
    </div>
