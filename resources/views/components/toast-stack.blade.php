@php
    $status = session('status');
    $flash = session('toast');

    if ($flash === null && is_string($status) && $status !== '') {
        $flash = [
            'type' => 'success',
            'message' => $status === 'verification-link-sent'
                ? 'Verification email sent. Please check your inbox.'
                : $status,
        ];
    }
@endphp

<div
    class="logic-toast-stack"
    data-logicstrand-toasts
    data-flash='@json($flash)'
    role="region"
    aria-label="Notifications"
    aria-live="polite"
></div>
