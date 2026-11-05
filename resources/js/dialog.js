const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), summary, [tabindex]:not([tabindex="-1"])';

/** Keeps Tab and Shift+Tab inside an open panel. */
export function trapFocus(panel, event) {
    if (event.key !== 'Tab' || panel.hidden) return;
    const items = [...panel.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null || el === document.activeElement);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

const smallScreen = window.matchMedia('(max-width: 39.999rem)');

/** Prevents the page behind a full-screen panel from scrolling on phones. */
export function lockScroll(lock) {
    document.documentElement.style.overflow = lock && smallScreen.matches ? 'hidden' : '';
}

export function storage(kind = 'localStorage') {
    return {
        get(key, fallback) {
            try {
                const value = JSON.parse(window[kind].getItem(key));
                return value ?? fallback;
            } catch {
                return fallback;
            }
        },
        set(key, value) {
            try {
                window[kind].setItem(key, JSON.stringify(value));
            } catch {
                // Storage can be unavailable (private mode); settings still apply on this page.
            }
        },
        remove(key) {
            try {
                window[kind].removeItem(key);
            } catch {
                // Ignore.
            }
        },
    };
}
