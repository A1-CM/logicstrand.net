import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';
import '@fortawesome/fontawesome-free/css/brands.min.css';

const toastSelector = '[data-logicstrand-toasts]';

function showToast(options = {}) {
    const root = document.querySelector(toastSelector);

    if (!root || !options.message) {
        return;
    }

    const type = ['success', 'error', 'info'].includes(options.type) ? options.type : 'info';
    const message = String(options.message).slice(0, 300);
    const labels = { success: 'Success', error: 'Attention', info: 'Notice' };
    const icons = { success: '✓', error: '!', info: '✳' };
    const title = String(options.title || labels[type]).slice(0, 60);
    const duration = Math.min(10000, Math.max(2500, Number(options.duration) || 5000));

    while (root.children.length >= 3) {
        root.firstElementChild.remove();
    }

    const toast = document.createElement('div');
    toast.className = 'logic-toast logic-toast-' + type;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

    const icon = document.createElement('span');
    icon.className = 'logic-toast-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = icons[type];

    const copy = document.createElement('span');
    copy.className = 'logic-toast-copy';

    const heading = document.createElement('strong');
    heading.textContent = title;

    const description = document.createElement('span');
    description.textContent = message;

    copy.append(heading, description);

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'logic-toast-close';
    close.setAttribute('aria-label', 'Dismiss notification');
    close.textContent = '×';

    let timer;
    const dismiss = () => {
        window.clearTimeout(timer);
        toast.classList.add('is-leaving');
        window.setTimeout(() => toast.remove(), 180);
    };

    close.addEventListener('click', dismiss);
    toast.append(icon, copy, close);
    root.append(toast);
    timer = window.setTimeout(dismiss, duration);

    return toast;
}

function consumeFlashToast() {
    const root = document.querySelector(toastSelector);

    if (!root || root.dataset.toastConsumed === 'true') {
        return;
    }

    root.dataset.toastConsumed = 'true';

    try {
        const flash = JSON.parse(root.dataset.flash || 'null');

        if (flash && typeof flash === 'object') {
            showToast(flash);
        }
    } catch {
        // A malformed flash message must not interrupt page navigation.
    }
}

window.LogicStrandToast = { show: showToast };
window.addEventListener('logicstrand:toast', (event) => showToast(event.detail || {}));
document.addEventListener('livewire:navigated', consumeFlashToast);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', consumeFlashToast, { once: true });
} else {
    consumeFlashToast();
}
