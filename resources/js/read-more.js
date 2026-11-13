/**
 * "Read more" toggles for long text blocks.
 *
 * Markup: <div data-read-more style="--read-more-lines: 2"><p data-read-more-text id="x">…</p>
 * <button data-read-more-toggle aria-controls="x" hidden>Read more</button></div>
 *
 * Without JavaScript the full text shows. With it, the text is clamped only when it is
 * actually longer than the line limit, so short text never gets a pointless button.
 */
export function initReadMore() {
    const blocks = [...document.querySelectorAll('[data-read-more]')];
    if (!blocks.length) return;

    const measure = (block) => {
        const text = block.querySelector('[data-read-more-text]');
        const button = block.querySelector('[data-read-more-toggle]');
        if (!text || !button || block.dataset.expanded === 'true') return;

        block.classList.add('is-clamped');
        const overflows = text.scrollHeight - text.clientHeight > 1;
        block.classList.toggle('is-clamped', overflows);
        button.hidden = !overflows;
    };

    blocks.forEach((block) => {
        const button = block.querySelector('[data-read-more-toggle]');
        button?.addEventListener('click', () => {
            const expand = block.dataset.expanded !== 'true';
            block.dataset.expanded = String(expand);
            block.classList.toggle('is-clamped', !expand);
            button.setAttribute('aria-expanded', String(expand));
            button.querySelector('[data-read-more-label]').textContent = expand ? 'Read less' : 'Read more';
            button.querySelector('i')?.classList.toggle('rotate-180', expand);
        });
        measure(block);
    });

    let frame;
    window.addEventListener('resize', () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => blocks.forEach(measure));
    });
}
