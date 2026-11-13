/**
 * Scroll reveal: content below the fold fades and slides in as it enters the viewport, and
 * items in a grid appear one after another.
 *
 * Safe by default: content is only hidden once this script runs, anything already on screen
 * is left alone (no flash on load), and nothing moves for people who prefer reduced motion
 * (system setting or the site's own accessibility panel).
 */
const TARGETS = [
    '[data-reveal]',
    'main .section-body > *',
    'main .section > .container-site > h2',
    'main #service-groups > *',
    'main .section .grid > .card',
    'main .section .grid > .card-hover',
].join(',');

function reducedMotion() {
    return matchMedia('(prefers-reduced-motion: reduce)').matches
        || document.documentElement.classList.contains('a11y-reduced-motion');
}

export function initReveal() {
    if (reducedMotion() || !('IntersectionObserver' in window)) return;

    const below = window.innerHeight * 0.95;
    const elements = [...new Set(document.querySelectorAll(TARGETS))]
        // Skip what the visitor can already see, and anything inside the header or a dialog.
        .filter((element) => element.getBoundingClientRect().top > below && !element.closest('header, dialog, [role="dialog"]'));

    if (!elements.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const element = entry.target;
                element.classList.add('is-revealed');
                observer.unobserve(element);
                // Hand control back to the element's own transitions (e.g. card hover lift).
                setTimeout(() => {
                    element.classList.remove('reveal', 'is-revealed');
                    element.style.removeProperty('--reveal-delay');
                }, 900);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );

    elements.forEach((element) => {
        // Stagger siblings in the same grid, capped so long lists do not lag.
        const index = [...element.parentElement.children].indexOf(element);
        element.style.setProperty('--reveal-delay', `${Math.min(index % 6, 5) * 70}ms`);
        element.classList.add('reveal');
        observer.observe(element);
    });

    // If reduced motion is switched on later from the accessibility panel, show everything.
    new MutationObserver(() => {
        if (!reducedMotion()) return;
        elements.forEach((element) => element.classList.add('is-revealed'));
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}
