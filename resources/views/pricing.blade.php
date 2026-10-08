<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'Pricing'])
    <meta name="description" content="Choose a LogicStrand plan and follow every answer back to its source.">
</head>
<body class="site-body">
    <div class="site-shell">
        <header class="site-header site-container">
            <a href="{{ route('home') }}" class="site-brand" aria-label="LogicStrand home"><x-app-logo-icon class="brand-mark" /><span>LogicStrand<span class="brand-period">.</span></span></a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="{{ route('home') }}#platform">Platform</a>
                <a href="{{ route('home') }}#use-cases">Who it helps</a>
                <a href="{{ route('home') }}#how-it-works">How it works</a>
                <a href="{{ route('home') }}#voices">Perspectives</a>
                <a href="{{ route('pricing') }}" aria-current="page">Pricing</a>
                <a href="{{ route('home') }}#contact">Contact</a>
            </nav>
            <div class="site-header-actions">
                @auth
                    <a class="site-login" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="site-login" href="{{ route('login') }}">Sign in</a>
                @endauth
                <a class="button button-dark button-small" href="{{ route('checkout.show', 'sandbox') }}">Start Sandbox <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
            </div>
        </header>

        <main>
            <section class="pricing-hero site-container">
                <div class="pricing-hero-copy">
                    <span class="section-kicker">A CLEAR PLACE TO BEGIN</span>
                    <h1>Choose a clearer<br><em>way forward.</em></h1>
                    <p>Start with seven days in Sandbox. When your questions grow, choose more room for documents and answers.</p>
                    <div class="pricing-hero-note"><span>✳</span> Every plan keeps the evidence in view.</div>
                </div>
                <img src="{{ asset('images/strand-library.webp') }}" alt="A cobalt strand connecting two stacks of research pages" width="1400" height="933" fetchpriority="high">
            </section>

            <section class="site-container pricing-section" aria-label="Plans">
                <div class="pricing-head"><span class="section-kicker">01 / FIND YOUR FIT</span><p>One personal workspace. Three ways to make sense of what you know.</p></div>
                <div class="pricing-grid">
                    @foreach($plans as $slug => $plan)
                        <article class="pricing-card {{ $slug === 'sandbox' ? 'pricing-card-featured' : '' }}">
                            <div class="pricing-card-top">
                                <span class="pricing-card-kicker">{{ $plan['eyebrow'] }}</span>
                                @if($slug === 'sandbox') <span class="pricing-tag">START HERE</span> @endif
                            </div>
                            <h2>{{ $plan['name'] }}</h2>
                            <p class="pricing-description">{{ $plan['description'] }}</p>
                            <div class="pricing-amount"><strong>&#36;{{ $plan['price'] }}</strong><span>/ {{ $plan['period'] }}</span></div>
                            <a href="{{ route('checkout.show', $slug) }}" class="button {{ $slug === 'sandbox' ? 'button-blue' : 'button-dark' }} pricing-action">{{ $plan['action'] }} <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
                            <div class="pricing-rule"></div>
                            <span class="pricing-includes">WHAT'S INCLUDED</span>
                            <ul>
                                @foreach($plan['features'] as $feature)
                                    <li><span aria-hidden="true">✓</span>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach
                </div>
                <p class="pricing-footnote">Plans are for individual accounts. Access ends on the date shown in your workspace unless you choose another period.</p>
            </section>

            <section class="pricing-detail site-container">
                <div><span class="section-kicker">02 / MORE THAN AN ANSWER</span><h2>Make the source<br><em>part of the story.</em></h2><p>Upload what matters, ask a question in plain language, and inspect the passages that informed the response.</p><a href="{{ route('home') }}#evidence" class="text-link">See the evidence path <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a></div>
                <img src="{{ asset('images/strand-worktable.webp') }}" alt="An open research binder with a blue strand crossing the workspace" width="1400" height="933" loading="lazy">
            </section>

            <section class="pricing-questions site-container">
                <span class="section-kicker">03 / THE DETAILS</span>
                <h2>Good questions,<br><em>clear answers.</em></h2>
                <div class="pricing-question-grid">
                    <div><strong>When does Sandbox begin?</strong><p>Your seven days begin when you complete checkout. Your dashboard shows the end date and days remaining.</p></div>
                    <div><strong>What happens when access ends?</strong><p>Your saved documents and answers remain in your account. Choose a new plan period to keep uploading and asking questions.</p></div>
                    <div><strong>What files can I use?</strong><p>UTF-8 text files and text-based PDFs up to 10 MB each. Scanned PDFs need OCR and are not supported yet.</p></div>
                    <div><strong>How are answers created?</strong><p>Relevant passages are sent to Groq for an answer. You can inspect cited passages beside the result.</p></div>
                </div>
            </section>
        </main>

        @include('partials.site-footer')
    </div>
    @include('partials.cookie-consent')
    <x-toast-stack />
</body>
</html>
