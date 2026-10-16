@if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')))
    <div class="google-auth-option">
        <div class="google-auth-divider"><span>or</span></div>
        <a href="{{ route('auth.google.redirect') }}" class="google-auth-button">
            <i class="fa-brands fa-google" aria-hidden="true"></i>
            <span>{{ $label ?? 'Continue with Google' }}</span>
        </a>
    </div>
@endif
