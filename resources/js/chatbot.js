import { lockScroll, storage, trapFocus } from './dialog';

const HISTORY_KEY = 'site-chat-history';
const MAX_FILE_BYTES = 10 * 1024 * 1024;
const AVATAR_CLASS = 'grid size-8 shrink-0 place-items-center overflow-hidden rounded-full border border-line bg-white';

const timeFormat = new Intl.DateTimeFormat(undefined, { hour: 'numeric', minute: '2-digit' });

export function initChatbot() {
    document.querySelectorAll('[data-chatbot]').forEach(setUpWidget);
}

function setUpWidget(widget) {
    const toggle = widget.querySelector('[data-chatbot-toggle]');
    const panel = widget.querySelector('#chatbot-panel');
    const form = widget.querySelector('[data-chatbot-form]');
    const enabled = widget.dataset.enabled === 'true';
    if (!toggle || !panel) return;

    const setOpen = (open, { restoreFocus = true } = {}) => {
        toggle.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        lockScroll(open);
        if (open) {
            scrollToEnd();
            const input = form?.elements.message;
            (input && !input.disabled ? input : panel.querySelector('[data-chatbot-close]'))?.focus();
        } else if (restoreFocus) {
            toggle.focus();
        }
    };

    const scroller = widget.querySelector('[data-chatbot-scroll]');
    const scrollToEnd = () => scroller && (scroller.scrollTop = scroller.scrollHeight);

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    widget.querySelector('[data-chatbot-close]')?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.altKey && !event.ctrlKey && !event.metaKey && event.code === 'KeyC') {
            event.preventDefault();
            return setOpen(panel.hidden);
        }
        if (panel.hidden) return;
        if (event.key === 'Escape') return setOpen(false);
        trapFocus(panel, event);
    });

    if (!form || !enabled) return;

    const history = storage('sessionStorage');
    const input = form.elements.message;
    const attachment = form.elements.attachment;
    const submitButton = form.querySelector('button[type="submit"]');
    const fileChip = form.querySelector('[data-chatbot-file]');
    const fileName = form.querySelector('[data-chatbot-file-name]');
    const errorBox = form.querySelector('[data-chatbot-error]');
    const log = widget.querySelector('[data-chatbot-log]');
    const welcome = log.firstElementChild;
    const suggestions = widget.querySelector('[data-chatbot-suggestions]');

    /** The assistant's avatar: the site favicon/logo when set, otherwise a robot icon. */
    const avatar = () => {
        const wrap = document.createElement('span');
        wrap.className = AVATAR_CLASS;
        wrap.setAttribute('aria-hidden', 'true');
        if (widget.dataset.avatar) {
            const img = document.createElement('img');
            img.src = widget.dataset.avatar;
            img.alt = '';
            img.className = 'size-full object-cover';
            wrap.append(img);
        } else {
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-robot text-sm text-secondary';
            wrap.append(icon);
        }
        return wrap;
    };
    let messages = history.get(HISTORY_KEY, []).filter((m) => m && typeof m.content === 'string' && ['user', 'assistant'].includes(m.role));
    let sending = false;

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.toggle('hidden', !message);
    };

    const autosize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 128)}px`;
    };

    function bubble(role, content, { time = new Date(), attachmentName = null } = {}) {
        const row = document.createElement('div');
        row.className = `flex items-end gap-2.5 animate-fade-up ${role === 'user' ? 'flex-row-reverse' : ''}`;

        if (role !== 'user') row.append(avatar());

        const wrap = document.createElement('div');
        wrap.className = `flex max-w-[85%] flex-col gap-1 ${role === 'user' ? 'items-end' : 'items-start'}`;

        const body = document.createElement('div');
        body.className = role === 'user'
            ? 'rounded-2xl rounded-br-md bg-brand px-4 py-2.5 text-sm leading-6 text-white shadow-sm'
            : 'rounded-2xl rounded-bl-md border border-line bg-white px-4 py-3 text-sm leading-6 text-ink shadow-sm';

        const sr = document.createElement('span');
        sr.className = 'sr-only';
        sr.textContent = role === 'user' ? 'You said: ' : 'Assistant: ';
        const text = document.createElement('p');
        text.className = 'whitespace-pre-wrap break-words';
        text.textContent = content;
        body.append(sr, text);

        if (attachmentName) {
            const tag = document.createElement('p');
            tag.className = 'mt-2 inline-flex max-w-full items-center gap-1.5 rounded-lg bg-white/15 px-2 py-1 text-xs';
            const clip = document.createElement('i');
            clip.className = 'fa-solid fa-paperclip';
            clip.setAttribute('aria-hidden', 'true');
            tag.append(clip, document.createTextNode(attachmentName));
            body.append(tag);
        }

        const meta = document.createElement('time');
        meta.className = 'px-1 text-[0.6875rem] text-muted';
        meta.dateTime = new Date(time).toISOString();
        meta.textContent = timeFormat.format(new Date(time));

        wrap.append(body, meta);
        row.append(wrap);
        log.append(row);
        scrollToEnd();
        return { row, text };
    }

    function typing() {
        const row = document.createElement('div');
        row.className = 'flex items-end gap-2.5';
        row.append(avatar());
        row.insertAdjacentHTML('beforeend', '<div class="flex items-center gap-1 rounded-2xl rounded-bl-md border border-line bg-white px-4 py-3.5 shadow-sm">'
            + '<span class="sr-only">Assistant is typing</span>'
            + [0, 150, 300].map((delay) => `<span aria-hidden="true" class="size-2 rounded-full bg-muted animate-typing" style="animation-delay:${delay}ms"></span>`).join('')
            + '</div>');
        log.append(row);
        scrollToEnd();
        return row;
    }

    const persist = () => history.set(HISTORY_KEY, messages.slice(-30));
    const updateSuggestions = () => suggestions?.classList.toggle('hidden', messages.length > 0);

    // Restore the conversation when moving between pages.
    messages.forEach((m) => bubble(m.role, m.content, { time: m.time || Date.now(), attachmentName: m.attachmentName }));
    updateSuggestions();

    const clearFile = () => {
        attachment.value = '';
        fileName.textContent = '';
        fileChip.classList.add('hidden');
        fileChip.classList.remove('flex');
    };

    attachment.addEventListener('change', () => {
        const file = attachment.files[0];
        showError('');
        if (file && file.size > MAX_FILE_BYTES) {
            clearFile();
            return showError('That file is larger than the 10 MB limit.');
        }
        if (!file) return clearFile();
        fileName.textContent = `Attached: ${file.name}`;
        fileChip.classList.remove('hidden');
        fileChip.classList.add('flex');
        input.focus();
    });
    form.querySelector('[data-chatbot-file-remove]')?.addEventListener('click', () => {
        clearFile();
        input.focus();
    });

    input.addEventListener('input', autosize);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    suggestions?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-chatbot-suggestion]');
        if (!button) return;
        input.value = button.textContent.trim();
        form.requestSubmit();
    });

    widget.querySelector('[data-chatbot-reset]')?.addEventListener('click', () => {
        messages = [];
        persist();
        [...log.children].forEach((child) => child !== welcome && child.remove());
        clearFile();
        showError('');
        updateSuggestions();
        input.focus();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (sending) return;
        const text = input.value.trim();
        const file = attachment.files[0];
        if (!text && !file) return;

        showError('');
        const content = text || `Please review the attached file: ${file.name}`;
        const userMessage = { role: 'user', content, time: Date.now(), attachmentName: file?.name ?? null };
        messages.push(userMessage);
        const sent = bubble('user', content, userMessage);
        updateSuggestions();
        input.value = '';
        autosize();

        sending = true;
        submitButton.disabled = true;
        const indicator = typing();

        try {
            const payload = new FormData();
            messages.slice(-12).forEach((message, index) => {
                payload.append(`messages[${index}][role]`, message.role);
                payload.append(`messages[${index}][content]`, message.content);
            });
            if (file) payload.append('attachment', file);
            const response = await fetch(widget.dataset.endpoint, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                body: payload,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'The assistant is temporarily unavailable. Please try again.');

            indicator.remove();
            const reply = { role: 'assistant', content: String(data.reply ?? ''), time: Date.now() };
            messages.push(reply);
            bubble('assistant', reply.content, reply);
            clearFile();
        } catch (error) {
            indicator.remove();
            // Drop the unanswered message so a retry does not send it twice.
            messages.pop();
            sent.row.remove();
            updateSuggestions();
            input.value = text;
            autosize();
            showError(error.message || 'The assistant is temporarily unavailable. Please try again.');
        } finally {
            persist();
            sending = false;
            submitButton.disabled = false;
            input.focus();
            scrollToEnd();
        }
    });
}
