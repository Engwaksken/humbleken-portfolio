/**
 * Services page: collapsible service areas built on <details>, so they work without JavaScript.
 * The script adds an "Expand all / Collapse all" button, opens an area when its quick link is
 * used or the address points at it (#category-networking), and keeps the button label in step.
 */
export function initServicesAccordion() {
    const container = document.getElementById('service-groups');
    if (!container) return;

    const groups = [...container.querySelectorAll('details.service-group')];
    const toggleAll = document.querySelector('[data-services-toggle-all]');

    const syncToggle = () => {
        if (!toggleAll) return;
        const allOpen = groups.every((group) => group.open);
        toggleAll.querySelector('[data-toggle-all-label]').textContent = allOpen ? 'Collapse all' : 'Expand all';
        toggleAll.setAttribute('aria-expanded', String(allOpen));
        const icon = toggleAll.querySelector('[data-toggle-all-icon]');
        icon?.classList.toggle('fa-up-right-and-down-left-from-center', !allOpen);
        icon?.classList.toggle('fa-down-left-and-up-right-to-center', allOpen);
    };

    const openGroup = (id, { scroll = true } = {}) => {
        const group = document.getElementById(id);
        if (!group || !groups.includes(group)) return;
        group.open = true;
        if (scroll) group.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
        group.querySelector('summary')?.focus({ preventScroll: true });
    };

    if (toggleAll) {
        toggleAll.hidden = false;
        toggleAll.addEventListener('click', () => {
            const open = !groups.every((group) => group.open);
            groups.forEach((group) => (group.open = open));
            syncToggle();
        });
    }

    groups.forEach((group) => group.addEventListener('toggle', syncToggle));

    document.querySelectorAll('[data-open-group]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const id = link.dataset.openGroup;
            history.replaceState(null, '', `#${id}`);
            openGroup(id);
        });
    });

    const fromHash = () => {
        const id = decodeURIComponent(location.hash.slice(1));
        if (id) openGroup(id);
    };
    window.addEventListener('hashchange', fromHash);
    if (location.hash) fromHash();

    syncToggle();
}
