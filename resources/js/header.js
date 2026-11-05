/**
 * Sticky header: adds a shadow once the page scrolls and drives the mobile menu.
 */
export function initHeader() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    const setScrolled = () => header.setAttribute('data-scrolled', String(window.scrollY > 4));
    setScrolled();
    window.addEventListener('scroll', setScrolled, { passive: true });

    const toggle = header.querySelector('[data-menu-toggle]');
    const menu = header.querySelector('#mobile-menu');
    if (!toggle || !menu) return;

    const setOpen = (open, { restoreFocus = false } = {}) => {
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        toggle.querySelector('[data-menu-icon-open]')?.classList.toggle('hidden', open);
        toggle.querySelector('[data-menu-icon-close]')?.classList.toggle('hidden', !open);
        menu.hidden = !open;
        if (open) menu.querySelector('a, button')?.focus();
        if (!open && restoreFocus) toggle.focus();
    };

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) setOpen(false, { restoreFocus: true });
    });
    window.matchMedia('(min-width: 64rem)').addEventListener('change', (event) => {
        if (event.matches) setOpen(false);
    });
}
