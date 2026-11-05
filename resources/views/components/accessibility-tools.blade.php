<div class="fixed bottom-4 left-4 z-[60] sm:bottom-6 sm:left-6" data-accessibility-tools>
    <button type="button" aria-expanded="false" aria-controls="accessibility-panel" aria-label="Open accessibility tools" title="Accessibility tools" class="grid size-14 place-items-center rounded-full bg-[#14263d] text-white shadow-lg ring-2 ring-white hover:bg-[#102e45] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand" data-accessibility-toggle>
        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-8 fill-none stroke-current" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="8" r="1.25"/><path d="M12 11v6m-4-5 4 1 4-1m-7 6 3-3 3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <section id="accessibility-panel" aria-label="Accessibility tools" hidden class="absolute bottom-14 left-0 w-64 rounded-lg border border-[#14263d]/20 bg-white p-4 text-[#14263d] shadow-xl">
        <div class="flex items-center justify-between gap-3"><h2 class="font-semibold">Accessibility tools</h2><button type="button" class="rounded px-2 py-1 text-lg leading-none focus-visible:outline-2 focus-visible:outline-brand" aria-label="Close accessibility tools" data-accessibility-close>×</button></div>
        <div class="mt-3 grid grid-cols-2 gap-2">
            <button type="button" data-accessibility-action="text-larger" class="min-h-11 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand">Larger text</button>
            <button type="button" data-accessibility-action="text-reset" class="min-h-11 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand">Reset text</button>
            <button type="button" data-accessibility-action="contrast" aria-pressed="false" class="min-h-11 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand">High contrast</button>
            <button type="button" data-accessibility-action="underline" aria-pressed="false" class="min-h-11 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand">Underline links</button>
            <button type="button" data-accessibility-action="motion" aria-pressed="false" class="col-span-2 min-h-11 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand">Reduce motion</button>
            <button type="button" data-accessibility-action="reset" class="col-span-2 min-h-11 rounded border px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-brand">Reset all tools</button>
        </div>
        <p class="sr-only" data-accessibility-status role="status" aria-live="polite"></p>
    </section>
</div>
