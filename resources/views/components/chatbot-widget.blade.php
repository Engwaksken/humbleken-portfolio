@if (\App\Support\AiChat::enabled())
    <div class="fixed bottom-5 right-5 z-40" data-chatbot data-endpoint="{{ route('chatbot.messages') }}">
        <button type="button" aria-expanded="false" aria-controls="chatbot-panel" class="rounded-full bg-brand px-5 py-3 font-semibold text-white shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand" data-chatbot-toggle>Chat with us</button>
        <section id="chatbot-panel" aria-label="Website assistant" hidden class="absolute bottom-14 right-0 flex h-[min(70vh,34rem)] w-[min(90vw,23rem)] flex-col overflow-hidden rounded-xl border border-[#14263d]/20 bg-white text-[#14263d] shadow-2xl">
            <header class="flex items-center justify-between bg-[#14263d] px-4 py-3 text-white"><h2 class="font-semibold">Website assistant</h2><button type="button" aria-label="Close assistant" data-chatbot-close class="rounded px-2 text-xl">×</button></header>
            <div class="flex-1 space-y-3 overflow-y-auto p-4" aria-live="polite" aria-relevant="additions text" data-chatbot-log><p class="rounded bg-brand-tint p-3 text-sm">Ask me about our services, projects, or articles.</p></div>
            <form class="flex gap-2 border-t p-3" data-chatbot-form><label class="sr-only" for="chatbot-message">Your message</label><input id="chatbot-message" name="message" maxlength="2000" autocomplete="off" required class="min-w-0 flex-1 rounded border px-3 py-2 text-sm" placeholder="Type your question"><button type="submit" class="rounded bg-brand px-3 py-2 text-sm font-semibold text-white">Send</button></form>
            <p class="px-3 pb-2 text-xs text-[#506176]">AI-generated answers may be inaccurate. Don’t share sensitive information.</p>
        </section>
    </div>
@endif
