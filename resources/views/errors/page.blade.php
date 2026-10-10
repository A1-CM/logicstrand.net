<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title, 'forceLight' => true])
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="site-body error-body">
    <div class="error-shell site-container">
        <header class="error-header">
            <a href="{{ route('home') }}" class="site-brand" aria-label="LogicStrand home">
                <img class="brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899">
            </a>
            <a class="error-header-link" href="{{ route('pricing') }}">Explore LogicStrand <span aria-hidden="true">↗</span></a>
        </header>
        <main class="error-main">
            <div class="error-orbit" aria-hidden="true">
                <svg viewBox="0 0 480 300" fill="none">
                    <path d="M-25 230C93 240 110 62 224 111C330 157 307 278 507 61" stroke="currentColor" stroke-width="2"/>
                    <path d="M-15 275C102 179 144 125 240 185C334 244 388 44 504 118" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="224" cy="111" r="6" fill="currentColor"/><circle cx="240" cy="185" r="5" fill="currentColor"/><circle cx="365" cy="146" r="5" fill="currentColor"/>
                </svg>
            </div>
            <section class="error-card" aria-labelledby="error-title">
                <span class="section-kicker">{{ $code }} / A THREAD INTERRUPTED</span>
                <h1 id="error-title">{{ $title }}</h1>
                <p>{{ $message }}</p>
                @if (isset($detail))<p class="error-detail">{{ $detail }}</p>@endif
                <div class="error-actions">
                    <a class="button button-blue" href="{{ $primaryUrl ?? route('home') }}">{{ $primaryText ?? 'Return home' }}</a>
                    @if (Route::has('login'))<a class="button button-light" href="{{ route('login') }}">Sign in</a>@endif
                </div>
                <a class="error-support" href="mailto:support@logicstrand.net">Need help? Contact LogicStrand support <span aria-hidden="true">↗</span></a>
            </section>
        </main>
        <footer class="error-footer"><span>© {{ date('Y') }} LogicStrand</span><span>Clarity begins with a question.</span></footer>
    </div>
</body>
</html>
