import './bootstrap';

const accessibilityKey = 'site-accessibility-preferences';
const root = document.documentElement;

function loadAccessibilityPreferences() {
    try {
        const stored = JSON.parse(localStorage.getItem(accessibilityKey) || '{}');
        return stored && typeof stored === 'object' ? stored : {};
    } catch {
        return {};
    }
}

function applyAccessibilityPreferences(preferences) {
    const textSize = Math.min(3, Math.max(0, Number(preferences.textSize) || 0));
    root.style.fontSize = textSize ? `${100 + textSize * 10}%` : '';
    root.classList.toggle('accessibility-high-contrast', Boolean(preferences.highContrast));
    root.classList.toggle('accessibility-underlined-links', Boolean(preferences.underlinedLinks));
    root.classList.toggle('accessibility-reduced-motion', Boolean(preferences.reducedMotion));
    document.querySelector('[data-accessibility-action="contrast"]')?.setAttribute('aria-pressed', String(Boolean(preferences.highContrast)));
    document.querySelector('[data-accessibility-action="underline"]')?.setAttribute('aria-pressed', String(Boolean(preferences.underlinedLinks)));
    document.querySelector('[data-accessibility-action="motion"]')?.setAttribute('aria-pressed', String(Boolean(preferences.reducedMotion)));
}

let accessibilityPreferences = loadAccessibilityPreferences();
applyAccessibilityPreferences(accessibilityPreferences);

const accessibilityTools = document.querySelector('[data-accessibility-tools]');
accessibilityTools?.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-accessibility-toggle]');
    const close = event.target.closest('[data-accessibility-close]');
    const action = event.target.closest('[data-accessibility-action]')?.dataset.accessibilityAction;
    const panel = accessibilityTools.querySelector('#accessibility-panel');
    const status = accessibilityTools.querySelector('[data-accessibility-status]');

    if (toggle) {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        if (open) panel.querySelector('[data-accessibility-action]')?.focus();
        return;
    }

    if (close) {
        panel.hidden = true;
        accessibilityTools.querySelector('[data-accessibility-toggle]')?.setAttribute('aria-expanded', 'false');
        accessibilityTools.querySelector('[data-accessibility-toggle]')?.focus();
        return;
    }

    if (!action) return;
    if (action === 'text-larger') accessibilityPreferences.textSize = Math.min(3, (Number(accessibilityPreferences.textSize) || 0) + 1);
    if (action === 'text-reset') accessibilityPreferences.textSize = 0;
    if (action === 'contrast') accessibilityPreferences.highContrast = !accessibilityPreferences.highContrast;
    if (action === 'underline') accessibilityPreferences.underlinedLinks = !accessibilityPreferences.underlinedLinks;
    if (action === 'motion') accessibilityPreferences.reducedMotion = !accessibilityPreferences.reducedMotion;
    if (action === 'reset') accessibilityPreferences = {};
    applyAccessibilityPreferences(accessibilityPreferences);
    try {
        localStorage.setItem(accessibilityKey, JSON.stringify(accessibilityPreferences));
    } catch {
        // Preferences still apply for this page when storage is unavailable.
    }
    const messages = {
        'text-larger': `Text size increased to ${100 + accessibilityPreferences.textSize * 10} percent.`,
        'text-reset': 'Text size reset.',
        contrast: accessibilityPreferences.highContrast ? 'High contrast enabled.' : 'High contrast disabled.',
        underline: accessibilityPreferences.underlinedLinks ? 'Link underlining enabled.' : 'Link underlining disabled.',
        motion: accessibilityPreferences.reducedMotion ? 'Reduced motion enabled.' : 'Reduced motion disabled.',
        reset: 'Accessibility preferences reset.',
    };
    if (status) status.textContent = messages[action] || 'Accessibility preference updated.';
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    const accessibilityPanel = accessibilityTools?.querySelector('#accessibility-panel');
    if (accessibilityPanel && !accessibilityPanel.hidden) {
        accessibilityPanel.hidden = true;
        accessibilityTools.querySelector('[data-accessibility-toggle]')?.setAttribute('aria-expanded', 'false');
        accessibilityTools.querySelector('[data-accessibility-toggle]')?.focus();
        return;
    }

    document.querySelectorAll('[data-chatbot]').forEach((widget) => {
        const panel = widget.querySelector('#chatbot-panel');
        if (!panel || panel.hidden) return;
        panel.hidden = true;
        widget.querySelector('[data-chatbot-toggle]')?.setAttribute('aria-expanded', 'false');
        widget.querySelector('[data-chatbot-toggle]')?.focus();
    });
});

document.querySelectorAll('[data-chatbot]').forEach((widget) => {
    const toggle = widget.querySelector('[data-chatbot-toggle]');
    const panel = widget.querySelector('#chatbot-panel');
    const form = widget.querySelector('[data-chatbot-form]');
    if (!form) {
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(open));
            panel.hidden = !open;
        });
        widget.querySelector('[data-chatbot-close]').addEventListener('click', () => {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        });
        return;
    }
    const input = form.elements.message;
    const attachment = form.elements.attachment;
    const attachmentName = form.querySelector('[data-chatbot-file-name]');
    const log = widget.querySelector('[data-chatbot-log]');
    const messages = [];

    const addMessage = (content, role) => {
        const bubble = document.createElement('p');
        bubble.className = `max-w-[90%] whitespace-pre-wrap rounded p-3 text-sm ${role === 'user' ? 'ml-auto bg-brand text-white' : 'bg-brand-tint'}`;
        bubble.textContent = content;
        log.append(bubble);
        log.scrollTop = log.scrollHeight;
        return bubble;
    };

    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        if (open && !input.disabled) input.focus();
    });
    widget.querySelector('[data-chatbot-close]').addEventListener('click', () => {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    });
    attachment.addEventListener('change', () => {
        const file = attachment.files[0];
        if (file && file.size > 10 * 1024 * 1024) {
            attachment.value = '';
            attachmentName.textContent = 'File exceeds the 10 MB limit.';
            attachmentName.classList.remove('hidden');
            return;
        }
        attachmentName.textContent = file ? `Attached: ${file.name}` : '';
        attachmentName.classList.toggle('hidden', !file);
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const text = input.value.trim();
        const file = attachment.files[0];
        if (!text && !file) return;
        input.value = '';
        const userMessage = text || `Please review the attached file: ${file.name}`;
        messages.push({ role: 'user', content: userMessage });
        addMessage(file ? `${userMessage}${text ? ` (attached: ${file.name})` : ''}` : userMessage, 'user');
        const pending = addMessage('Thinking…', 'assistant');
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        try {
            const payload = new FormData();
            messages.slice(-12).forEach((message, index) => {
                payload.append(`messages[${index}][role]`, message.role);
                payload.append(`messages[${index}][content]`, message.content);
            });
            if (file) payload.append('attachment', file);
            const response = await fetch(widget.dataset.endpoint, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: payload,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'The assistant is temporarily unavailable.');
            pending.textContent = data.reply;
            messages.push({ role: 'assistant', content: data.reply });
            attachment.value = '';
            attachmentName.textContent = '';
            attachmentName.classList.add('hidden');
        } catch (error) {
            pending.textContent = error.message || 'The assistant is temporarily unavailable. Please try again.';
            pending.setAttribute('role', 'alert');
        } finally {
            submitButton.disabled = false;
            if (!input.disabled) input.focus();
            log.scrollTop = log.scrollHeight;
        }
    });
});
