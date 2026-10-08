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

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
}

function postProcessing(url, row) {
    if (!url || row.dataset.processStarted === 'true') return;
    row.dataset.processStarted = 'true';
    fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' } })
        .then((response) => response.json().then((data) => ({ response, data })))
        .then(({ response, data }) => {
            if (!response.ok && response.status !== 409) showToast({ type: 'error', message: data.message || 'Could not start document processing.' });
        })
        .catch(() => showToast({ type: 'error', message: 'Could not start document processing. Please retry.' }));
}

function watchDocuments() {
    document.querySelectorAll('[data-document-progress]').forEach((row) => {
        if (row.dataset.stage === 'queued' && row.dataset.autoStart === 'true') postProcessing(row.dataset.processUrl, row);
        const resume = row.querySelector('[data-resume-document]');
        const copy = row.querySelector('[data-progress-copy]');
        const poll = async () => {
            try {
                const response = await fetch(row.dataset.statusUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!response.ok) return;
                const state = await response.json();
                const stages = { queued: 'Waiting to start', extracting: 'Reading text from your document', indexing: 'Organizing passages for search', ready: 'Your document is ready', failed: 'Processing needs attention' };
                if (copy) copy.textContent = stages[state.stage] || 'Preparing your document';
                if (state.status === 'ready' || state.status === 'failed') {
                    window.location.reload();
                    return;
                }
                const elapsed = Number(state.elapsed_seconds) || 0;
                if (elapsed >= 120 && copy) copy.textContent += ' · Taking longer than expected';
                if (resume) resume.hidden = elapsed < 300;
            } catch {
                // Polling is best effort; the page remains usable and can be refreshed.
            }
        };
        if (resume && !resume.dataset.bound) {
            resume.dataset.bound = 'true';
            resume.addEventListener('click', () => postProcessing(row.dataset.processUrl, row));
        }
        if (!row.dataset.polling) {
            row.dataset.polling = 'true';
            poll();
            window.setInterval(poll, 2500);
        }
    });
}

function submitUploadWithProgress(form) {
    const input = form.querySelector('[data-file-input]');
    const button = form.querySelector('[type="submit"]');
    if (!input?.files?.length || !button) return false;
    const payload = new FormData(form);
    const xhr = new XMLHttpRequest();
    button.disabled = true;
    button.textContent = 'Uploading…';
    xhr.open('POST', form.action);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
    xhr.upload.addEventListener('progress', (event) => {
        if (event.lengthComputable) button.textContent = `Uploading ${Math.round(event.loaded / event.total * 100)}%…`;
    });
    xhr.addEventListener('load', () => {
        let result = {};
        try { result = JSON.parse(xhr.responseText); } catch { /* handled below */ }
        if (xhr.status >= 200 && xhr.status < 300 && result.redirect) {
            window.location.assign(result.redirect);
            return;
        }
        const errors = Object.values(result.errors || {}).flat();
        showToast({ type: 'error', message: errors[0] || result.message || 'Upload failed. Please try again.' });
        button.disabled = false;
        button.textContent = 'Upload document';
    });
    xhr.addEventListener('error', () => {
        showToast({ type: 'error', message: 'Upload failed. Check your connection and try again.' });
        button.disabled = false;
        button.textContent = 'Upload document';
    });
    xhr.send(payload);
    return true;
}

document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-file-input]');
    if (!input) return;
    const label = input.closest('.file-drop')?.querySelector('[data-file-name]');
    if (label) label.textContent = input.files?.[0]?.name || 'No file selected';
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.matches('[data-async-upload]')) {
        event.preventDefault();
        submitUploadWithProgress(form);
        return;
    }
    if (form.matches('[data-confirm-delete]')) {
        const name = form.dataset.documentName || 'this document';
        if (!window.confirm('Remove "' + name + '"? This also deletes answers that cite it. This cannot be undone.')) {
            event.preventDefault();
            return;
        }
    }
    if (!form.matches('[data-busy-form]')) return;
    const button = form.querySelector('[type="submit"]');
    if (!button || button.disabled) {
        event.preventDefault();
        return;
    }
    button.disabled = true;
    button.classList.add('is-busy');
    button.textContent = button.dataset.busyLabel || 'Working…';
});

document.addEventListener('click', async (event) => {
    const example = event.target.closest('[data-question-example]');
    if (example) {
        const question = document.querySelector('#question');
        if (question) {
            question.value = example.dataset.questionExample;
            question.focus();
        }
        return;
    }

    const resume = event.target.closest('[data-resume-document]');
    if (resume) {
        const row = resume.closest('[data-document-progress]');
        if (row) { row.dataset.processStarted = 'false'; postProcessing(row.dataset.processUrl, row); }
        return;
    }
    const copy = event.target.closest('[data-copy-answer]');
    if (!copy) return;
    const answer = document.querySelector('#answer-text')?.textContent?.trim();
    if (!answer) return;
    try {
        await navigator.clipboard.writeText(answer);
        showToast({ type: 'success', message: 'Answer copied to clipboard.' });
    } catch {
        showToast({ type: 'error', message: 'We could not copy the answer. Please select the text instead.' });
    }
});

document.addEventListener('livewire:navigated', watchDocuments);
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', watchDocuments, { once: true });
} else {
    watchDocuments();
}
