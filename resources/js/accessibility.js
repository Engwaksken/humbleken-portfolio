import { lockScroll, storage, trapFocus } from './dialog';

const STORAGE_KEY = 'site-accessibility-preferences';
const MAX_TEXT_STEP = 5; // 100% → 150% in 10% steps
const MIN_TEXT_STEP = -1; // 90%

/** Preference key → class on <html>. */
const CLASSES = {
    highContrast: 'a11y-high-contrast',
    darkContrast: 'a11y-dark-contrast',
    invert: 'a11y-invert',
    grayscale: 'a11y-grayscale',
    lowSaturation: 'a11y-low-saturation',
    lineHeight: 'a11y-line-height',
    letterSpacing: 'a11y-letter-spacing',
    readableFont: 'a11y-readable-font',
    alignLeft: 'a11y-align-left',
    underlinedLinks: 'a11y-underlined-links',
    highlightLinks: 'a11y-highlight-links',
    highlightHeadings: 'a11y-highlight-headings',
    strongFocus: 'a11y-strong-focus',
    hideImages: 'a11y-hide-images',
    bigCursor: 'a11y-big-cursor',
    reducedMotion: 'a11y-reduced-motion',
};

/** Options that cannot be combined: enabling one switches the others off. */
const EXCLUSIVE = [
    ['highContrast', 'darkContrast', 'invert'],
    ['invert', 'grayscale', 'lowSaturation'],
    ['readingGuide', 'readingMask'],
];

const LABELS = {
    highContrast: 'High contrast',
    darkContrast: 'Dark contrast',
    invert: 'Inverted colours',
    grayscale: 'Monochrome',
    lowSaturation: 'Low saturation',
    lineHeight: 'Increased line height',
    letterSpacing: 'Increased text spacing',
    readableFont: 'Readable font',
    alignLeft: 'Left-aligned text',
    underlinedLinks: 'Link underlining',
    highlightLinks: 'Link highlighting',
    highlightHeadings: 'Heading highlighting',
    strongFocus: 'Focus highlight',
    hideImages: 'Hidden images',
    bigCursor: 'Big cursor',
    reducedMotion: 'Stopped animations',
    readingGuide: 'Reading guide',
    readingMask: 'Reading mask',
    readAloud: 'Read on click',
};

const PROFILES = {
    lowVision: { textSize: 3, highContrast: true, strongFocus: true, highlightLinks: true },
    dyslexia: { readableFont: true, letterSpacing: true, lineHeight: true, alignLeft: true, readingGuide: true },
    adhd: { readingMask: true, reducedMotion: true, highlightHeadings: true },
    seizure: { reducedMotion: true, lowSaturation: true },
    motor: { bigCursor: true, strongFocus: true, textSize: 1 },
    blind: { readAloud: true, underlinedLinks: true, strongFocus: true },
};

const READABLE_TEXT = 'p, h1, h2, h3, h4, h5, h6, li, a, button, label, dt, dd, td, th, blockquote, figcaption, summary, legend';

export function initAccessibility() {
    const root = document.documentElement;
    const store = storage();
    const speech = 'speechSynthesis' in window ? window.speechSynthesis : null;
    // The admin-chosen base size; the text-size tool scales relative to it.
    const baseFontSize = parseFloat(getComputedStyle(root).fontSize) || 16;
    let prefs = sanitize(store.get(STORAGE_KEY, {}));
    let guide = null;

    const tools = document.querySelector('[data-accessibility-tools]');
    const toggle = tools?.querySelector('[data-accessibility-toggle]');
    const panel = tools?.querySelector('#accessibility-panel');
    const backdrop = tools?.querySelector('[data-accessibility-backdrop]');
    const status = tools?.querySelector('[data-accessibility-status]');

    if (!speech) tools?.querySelectorAll('[data-requires-speech]').forEach((el) => el.remove());

    function sanitize(value) {
        return value && typeof value === 'object' && !Array.isArray(value) ? value : {};
    }

    function announce(message) {
        if (!status) return;
        status.textContent = '';
        window.setTimeout(() => (status.textContent = message), 30);
    }

    function textStep() {
        return Math.min(MAX_TEXT_STEP, Math.max(MIN_TEXT_STEP, Number(prefs.textSize) || 0));
    }

    function apply() {
        const step = textStep();
        root.style.fontSize = step ? `${baseFontSize * (1 + step * 0.1)}px` : '';
        Object.entries(CLASSES).forEach(([key, className]) => root.classList.toggle(className, Boolean(prefs[key])));

        if (prefs.readableFont && !document.getElementById('a11y-readable-font')) {
            const link = Object.assign(document.createElement('link'), {
                id: 'a11y-readable-font',
                rel: 'stylesheet',
                href: 'https://fonts.bunny.net/css?family=atkinson-hyperlegible:400,700&display=swap',
            });
            document.head.append(link);
        }

        applyReadingGuide();
        if (!prefs.readAloud) clearSpeaking();

        if (!tools) return;
        const percent = Math.round(100 + step * 10);
        tools.querySelector('[data-accessibility-text-size]')?.replaceChildren(`${percent}%`);
        const bar = tools.querySelector('[data-accessibility-text-bar]');
        if (bar) bar.style.width = `${((step - MIN_TEXT_STEP) / (MAX_TEXT_STEP - MIN_TEXT_STEP)) * 100}%`;
        tools.querySelectorAll('[data-accessibility-toggle-option]').forEach((button) => {
            button.setAttribute('aria-pressed', String(Boolean(prefs[button.dataset.accessibilityToggleOption])));
        });
        tools.querySelectorAll('[data-accessibility-profile]').forEach((button) => {
            button.setAttribute('aria-pressed', String(prefs.profile === button.dataset.accessibilityProfile));
        });
    }

    function save() {
        store.set(STORAGE_KEY, prefs);
        apply();
    }

    // ── Reading guide / mask ────────────────────────────────────────────
    function moveGuide(event) {
        const y = event.touches ? event.touches[0].clientY : event.clientY;
        if (!guide) return;
        if (guide.classList.contains('a11y-reading-guide')) guide.style.top = `${y}px`;
        else guide.style.setProperty('--mask-y', `${y}px`);
    }

    function applyReadingGuide() {
        const wanted = prefs.readingGuide ? 'a11y-reading-guide' : prefs.readingMask ? 'a11y-reading-mask' : null;
        if (guide && (!wanted || !guide.classList.contains(wanted))) {
            guide.remove();
            guide = null;
        }
        if (wanted && !guide) {
            guide = document.createElement('div');
            guide.className = wanted;
            guide.setAttribute('aria-hidden', 'true');
            guide.dataset.a11yExempt = '';
            guide.style.top = '50%';
            document.body.append(guide);
        }
    }

    window.addEventListener('pointermove', moveGuide, { passive: true });
    window.addEventListener('touchmove', moveGuide, { passive: true });

    // ── Text to speech ──────────────────────────────────────────────────
    let speakingEl = null;
    function clearSpeaking() {
        speech?.cancel();
        speakingEl?.classList.remove('a11y-speaking');
        speakingEl = null;
    }

    function speak(text, element = null) {
        if (!speech || !text.trim()) return;
        clearSpeaking();
        const utterance = new SpeechSynthesisUtterance(text.replace(/\s+/g, ' ').trim());
        utterance.lang = root.lang || 'en';
        utterance.rate = 0.95;
        if (element) {
            speakingEl = element;
            element.classList.add('a11y-speaking');
        }
        utterance.onend = () => {
            if (speakingEl === element) clearSpeaking();
        };
        speech.speak(utterance);
    }

    document.addEventListener('click', (event) => {
        if (!prefs.readAloud || event.target.closest('[data-accessibility-tools]')) return;
        const element = event.target.closest(READABLE_TEXT);
        if (element) speak(element.getAttribute('aria-label') || element.innerText, element);
    });
    document.addEventListener('focusin', (event) => {
        if (!prefs.readAloud || event.target.closest('[data-accessibility-tools]')) return;
        if (event.target.matches('a, button, input, select, textarea')) {
            const label = event.target.getAttribute('aria-label') || event.target.labels?.[0]?.innerText || event.target.innerText || event.target.placeholder || '';
            speak(label);
        }
    });

    // ── Panel ───────────────────────────────────────────────────────────
    function setOpen(open, { restoreFocus = true } = {}) {
        if (!panel) return;
        toggle.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        if (backdrop) backdrop.hidden = !open;
        lockScroll(open);
        if (open) panel.querySelector('[data-accessibility-close]')?.focus();
        else if (restoreFocus) toggle.focus();
    }

    toggle?.addEventListener('click', () => setOpen(panel.hidden));
    backdrop?.addEventListener('click', () => setOpen(false));

    tools?.addEventListener('click', (event) => {
        if (event.target.closest('[data-accessibility-close]')) return setOpen(false);

        const optionKey = event.target.closest('[data-accessibility-toggle-option]')?.dataset.accessibilityToggleOption;
        if (optionKey) {
            const enabled = !prefs[optionKey];
            if (enabled) EXCLUSIVE.filter((group) => group.includes(optionKey)).flat().forEach((key) => delete prefs[key]);
            prefs[optionKey] = enabled;
            delete prefs.profile;
            save();
            return announce(`${LABELS[optionKey]} ${enabled ? 'enabled' : 'disabled'}.`);
        }

        const profileKey = event.target.closest('[data-accessibility-profile]')?.dataset.accessibilityProfile;
        if (profileKey) {
            const label = event.target.closest('[data-accessibility-profile]').querySelector('span')?.textContent.trim();
            if (prefs.profile === profileKey) {
                prefs = {};
                save();
                return announce(`${label} profile turned off.`);
            }
            prefs = { ...PROFILES[profileKey], profile: profileKey };
            save();
            return announce(`${label} profile turned on.`);
        }

        const action = event.target.closest('[data-accessibility-action]')?.dataset.accessibilityAction;
        if (!action) return;
        if (action === 'text-larger' || action === 'text-smaller') {
            prefs.textSize = Math.min(MAX_TEXT_STEP, Math.max(MIN_TEXT_STEP, textStep() + (action === 'text-larger' ? 1 : -1)));
            save();
            return announce(`Text size ${Math.round(100 + prefs.textSize * 10)} percent.`);
        }
        if (action === 'speak-page') {
            const main = document.getElementById('main-content');
            return speak(main?.innerText || document.body.innerText);
        }
        if (action === 'speak-stop') return clearSpeaking();
        if (action === 'reset') {
            prefs = {};
            clearSpeaking();
            store.remove(STORAGE_KEY);
            apply();
            return announce('All accessibility settings have been reset.');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.altKey && !event.ctrlKey && !event.metaKey && event.code === 'KeyA') {
            event.preventDefault();
            return setOpen(panel?.hidden ?? false);
        }
        if (!panel || panel.hidden) return;
        if (event.key === 'Escape') return setOpen(false);
        trapFocus(panel, event);
    });

    document.addEventListener('pointerdown', (event) => {
        if (panel && !panel.hidden && !event.target.closest('[data-accessibility-tools]')) setOpen(false, { restoreFocus: false });
    });

    apply();
}
