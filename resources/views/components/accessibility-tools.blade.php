<div class="fixed bottom-5 left-5 z-40" data-accessibility-tools>
    <button type="button" aria-expanded="false" aria-controls="accessibility-panel" aria-label="Open accessibility tools" class="grid h-12 w-12 place-items-center rounded-full bg-[#14263d] text-xl font-bold text-white shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand" data-accessibility-toggle>
        <span aria-hidden="true">A</span>
    </button>
    <section id="accessibility-panel" aria-label="Accessibility tools" hidden class="absolute bottom-14 left-0 w-64 rounded-lg border border-[#14263d]/20 bg-white p-4 text-[#14263d] shadow-xl">
        <h2 class="font-semibold">Accessibility tools</h2>
        <div class="mt-3 grid grid-cols-2 gap-2">
            <button type="button" data-accessibility-action="text-larger" class="rounded border px-3 py-2 text-sm">Larger text</button>
            <button type="button" data-accessibility-action="text-reset" class="rounded border px-3 py-2 text-sm">Reset text</button>
            <button type="button" data-accessibility-action="contrast" aria-pressed="false" class="rounded border px-3 py-2 text-sm">High contrast</button>
            <button type="button" data-accessibility-action="underline" aria-pressed="false" class="rounded border px-3 py-2 text-sm">Underline links</button>
            <button type="button" data-accessibility-action="reset" class="col-span-2 rounded border px-3 py-2 text-sm">Reset all tools</button>
        </div>
    </section>
</div>
