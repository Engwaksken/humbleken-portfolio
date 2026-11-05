/**
 * Show/hide buttons for password fields. The button keeps an accurate label and
 * pressed state for screen readers, and fields are hidden again before submit so
 * browsers still offer to save the password.
 */
export function initPasswordToggles() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-password-toggle]');
        if (!button) return;
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;

        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(show));
        button.setAttribute('aria-label', `${show ? 'Hide' : 'Show'} ${button.dataset.label || 'password'}`);
        const icon = button.querySelector('[data-password-icon]');
        icon?.classList.toggle('fa-eye', !show);
        icon?.classList.toggle('fa-eye-slash', show);
    });

    document.addEventListener('submit', (event) => {
        event.target.querySelectorAll?.('[data-password-toggle][aria-pressed="true"]').forEach((button) => button.click());
    }, true);
}
