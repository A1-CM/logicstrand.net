<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['forceLight' => true])
</head>
<body class="logic-auth-body">
    <div class="auth-layout">
        <aside class="auth-story" aria-label="About LogicStrand">
            <a href="{{ route('home') }}" class="auth-story-brand">
                <span class="auth-story-logo"><x-app-logo-icon class="size-7" /></span>
                <span>LogicStrand<span class="auth-brand-period">.</span></span>
            </a>

            <div class="auth-story-content">
                <span class="auth-story-kicker"><span></span> KNOWLEDGE, CONNECTED</span>
                <h2>Find the thread<br>behind every<br><em>answer.</em></h2>
                <p>Bring your sources into focus. Ask what matters. See the evidence that gives every answer its shape.</p>
                <div class="auth-strand-visual" aria-hidden="true">
                    <svg viewBox="0 0 560 310" fill="none" preserveAspectRatio="xMidYMid slice">
                        <path d="M-32 247C139 273 190 46 296 101C405 158 389 298 591 35" stroke="#7190FF" stroke-width="1.5"/>
                        <path d="M-20 300C126 240 169 160 282 212C383 258 418 45 588 75" stroke="#9DABF3" stroke-width="1"/>
                        <path d="M-30 166C151 173 148 33 290 64C415 91 453 230 590 221" stroke="#667FCF" stroke-width="1"/>
                        <circle cx="296" cy="101" r="6" fill="#ABC0FF"/>
                        <circle cx="282" cy="212" r="5" fill="#ABC0FF"/>
                        <circle cx="420" cy="160" r="5" fill="#6A89FA"/>
                    </svg>
                    <span class="auth-strand-label">SOURCE → CONTEXT → CLARITY</span>
                </div>
            </div>

            <div class="auth-story-footer"><span>✳</span> A calmer way to work with what you know.</div>
        </aside>

        <main class="auth-content">
            <div class="auth-topline">
                <a href="{{ route('home') }}" class="auth-back">← <span>Back to site</span></a>
                <span class="auth-topline-note">YOUR KNOWLEDGE WORKSPACE</span>
            </div>
            <div class="auth-form-wrap">
                <a href="{{ route('home') }}" class="auth-mobile-brand" aria-label="LogicStrand home"><x-app-logo-icon class="size-8" /><span>LogicStrand.</span></a>
                <div class="auth-card">
                    {{ $slot }}
                </div>
            </div>
            <div class="auth-bottomline"><span>© {{ date('Y') }} LogicStrand</span><span>Clarity begins with a question.</span></div>
        </main>
    </div>

    <x-toast-stack />
    @fluxScripts
    @include('partials.cookie-consent')
</body>
</html>
