<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $pageTitle])
    <meta name="description" content="{{ $description }}">
</head>
<body class="site-body legal-body">
    <div class="site-shell">
        <header class="site-header site-container">
            <a href="{{ route('home') }}" class="site-brand" aria-label="LogicStrand home"><img class="brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899"></a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="{{ route('home') }}#platform">Platform</a>
                <a href="{{ route('pricing') }}">Pricing</a>
                <a href="{{ route('home') }}#contact">Contact</a>
            </nav>
            <div class="site-header-actions">
                @auth<a class="site-login" href="{{ route('dashboard') }}">Dashboard</a>@else<a class="site-login" href="{{ route('login') }}">Sign in</a>@endauth
                <a class="button button-dark button-small" href="{{ route('register') }}">Get started</a>
            </div>
        </header>
        <main class="site-container legal-main">
            <span class="section-kicker">LOGICSTRAND / {{ strtoupper($pageTitle) }}</span>
            <h1>{{ $pageTitle }}</h1>
            <p class="legal-updated">Last updated: October 8, 2026</p>
            <div class="legal-content">@yield('legal-content')</div>
        </main>
        @include('partials.site-footer')
    </div>
    @include('partials.cookie-consent')
</body>
</html>
