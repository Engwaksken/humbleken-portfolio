/**
 * Article page extras: reading progress bar, copy-link buttons and highlighting the
 * current section in the "On this page" list. Does nothing on other pages.
 */
export function initArticle() {
    initReadingProgress();
    initCopyLinks();
    initTocHighlight();
}

function initReadingProgress() {
    const bar = document.querySelector('[data-reading-progress]');
    const body = document.querySelector('[data-article-body]');
    if (!bar || !body) return;

    let frame;
    const update = () => {
        const rect = body.getBoundingClientRect();
        const total = rect.height - window.innerHeight;
        const progress = total <= 0 ? 1 : Math.min(1, Math.max(0, -rect.top / total));
        bar.style.transform = `scaleX(${progress})`;
    };
    const schedule = () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(update);
    };

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    update();
}

function initCopyLinks() {
    const buttons = document.querySelectorAll('[data-copy-link]');
    if (!buttons.length) return;

    // One shared live region announces the result to screen readers.
    const status = document.createElement('p');
    status.className = 'sr-only';
    status.setAttribute('aria-live', 'polite');
    document.body.append(status);

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const icon = button.querySelector('[data-copy-icon]');
            try {
                await navigator.clipboard.writeText(button.dataset.copyLink);
                status.textContent = 'Link copied.';
                icon?.classList.replace('fa-link', 'fa-check');
                button.setAttribute('aria-label', 'Link copied');
                setTimeout(() => {
                    icon?.classList.replace('fa-check', 'fa-link');
                    button.setAttribute('aria-label', 'Copy link');
                    status.textContent = '';
                }, 2000);
            } catch {
                status.textContent = 'Copying is not available. Copy the address from your browser instead.';
            }
        });
    });
}

function initTocHighlight() {
    const links = [...document.querySelectorAll('[data-toc-link]')];
    if (!links.length || !('IntersectionObserver' in window)) return;

    const headings = [...new Set(links.map((link) => link.dataset.tocLink))]
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    const setCurrent = (id) => {
        links.forEach((link) => link.setAttribute('aria-current', String(link.dataset.tocLink === id)));
    };

    const observer = new IntersectionObserver(
        (entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting);
            if (visible.length) setCurrent(visible[0].target.id);
        },
        { rootMargin: '-15% 0px -70% 0px' },
    );

    headings.forEach((heading) => observer.observe(heading));
}
