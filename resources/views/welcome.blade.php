<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'Reason with clarity'])
    <meta name="description" content="LogicStrand turns your documents into clear, source-backed answers.">
</head>
<body class="site-body">
    <div class="site-shell">
        <header class="site-header site-container">
            <a href="{{ route('home') }}" class="site-brand" aria-label="LogicStrand home">
                <x-app-logo-icon class="brand-mark" />
                <span>LogicStrand<span class="brand-period">.</span></span>
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="#platform">Platform</a>
                <a href="#how-it-works">How it works</a>
                <a href="#principles">Our approach</a>
            </nav>
            <div class="site-header-actions">
                @auth
                    <a class="site-login" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="site-login" href="{{ route('login') }}">Sign in</a>
                    <a class="button button-dark button-small" href="{{ route('register') }}">Get started <span aria-hidden="true">↗</span></a>
                @endauth
            </div>
        </header>

        <main>
            <section class="hero site-container">
                <div class="hero-copy">
                    <div class="eyebrow"><span class="eyebrow-dot"></span> INTELLIGENCE, WITH A LINE OF SIGHT</div>
                    <h1>Make every answer <em>traceable.</em></h1>
                    <p class="hero-lede">Bring your knowledge together. Ask better questions. Get clear answers connected to the source, so the path from information to insight is easier to follow.</p>
                    <div class="hero-actions">
                        <a href="{{ route('register') }}" class="button button-blue">Start your workspace <span aria-hidden="true">↗</span></a>
                        <a href="#how-it-works" class="text-link">See how it works <span aria-hidden="true">↓</span></a>
                    </div>
                    <div class="hero-note"><span class="note-line"></span> Your documents. Your questions. A clearer way forward.</div>
                </div>
                <div class="hero-visual" aria-label="Illustration of connected knowledge strands">
                    <div class="visual-orbit orbit-one"></div>
                    <div class="visual-orbit orbit-two"></div>
                    <div class="visual-grid"></div>
                    <svg class="strand-art" viewBox="0 0 570 530" fill="none" aria-hidden="true">
                        <path d="M-35 395C118 392 123 143 290 214C403 261 390 448 614 145" stroke="#B3BFD9" stroke-width="1.2"/>
                        <path d="M-44 450C151 390 181 273 287 312C410 357 408 138 613 94" stroke="#9CAEE5" stroke-width="1.2"/>
                        <path d="M-35 329C154 341 168 126 291 172C417 219 449 414 609 392" stroke="#718CE7" stroke-width="1.5"/>
                        <path d="M-20 511C104 493 158 461 240 393C381 277 479 330 610 258" stroke="#3C61DC" stroke-width="2"/>
                        <path d="M46 48C218 79 221 149 333 151C455 153 487 38 588 27" stroke="#D0D8E9" stroke-width="1.2"/>
                        <circle cx="291" cy="172" r="8" fill="#3157D8" stroke="#EDF1FF" stroke-width="7"/>
                        <circle cx="286" cy="313" r="7" fill="#3157D8" stroke="#EDF1FF" stroke-width="6"/>
                        <circle cx="240" cy="393" r="6" fill="#3157D8" stroke="#EDF1FF" stroke-width="6"/>
                        <circle cx="423" cy="333" r="5" fill="#3157D8" stroke="#EDF1FF" stroke-width="5"/>
                        <circle cx="119" cy="279" r="5" fill="#A2B3E3"/>
                        <circle cx="477" cy="213" r="5" fill="#A2B3E3"/>
                    </svg>
                    <div class="visual-tag visual-tag-one"><span class="tag-square"></span> Context, connected</div>
                    <div class="visual-tag visual-tag-two"><span class="tag-pulse"></span> Evidence in view</div>
                    <div class="visual-bottom"><span>LOGIC / 001</span><span>KNOWLEDGE IN MOTION</span></div>
                </div>
            </section>

            <div class="value-strip">
                <div class="site-container value-strip-inner">
                    <span>Designed for clarity</span>
                    <span class="strip-separator"></span>
                    <span>Grounded in your sources</span>
                    <span class="strip-separator"></span>
                    <span>Built around your questions</span>
                </div>
            </div>

            <section id="platform" class="section site-container platform-section">
                <div class="section-intro">
                    <span class="section-kicker">01 / THE PLATFORM</span>
                    <h2>Intelligence that<br><em>shows its work.</em></h2>
                    <p>Important knowledge lives across files and teams. LogicStrand gives it a more useful shape: a private space to connect documents, explore ideas, and keep the evidence close.</p>
                </div>
                <div class="preview-card">
                    <div class="preview-top"><span class="preview-logo"><x-app-logo-icon class="preview-icon" /> LogicStrand</span><span class="preview-label">ANSWER PREVIEW</span></div>
                    <div class="preview-question"><span class="preview-question-label">YOUR QUESTION</span><strong>What should we know before updating our renewal process?</strong></div>
                    <div class="preview-answer"><span class="preview-spark">✳</span><div><span class="preview-answer-label">A CLEARER ANSWER</span><p>Start with the documented approval steps and timing requirements. The sources below show the relevant passages, so your team can review the basis for the answer.</p></div></div>
                    <div class="preview-sources"><span>↳ &nbsp; Example policy · p. 2</span><span>↳ &nbsp; Process notes · p. 4</span></div>
                    <div class="preview-foot">Illustrative example — your workspace uses your own documents.</div>
                </div>
            </section>

            <section id="how-it-works" class="section workflow-section">
                <div class="site-container">
                    <div class="workflow-heading"><div><span class="section-kicker">02 / HOW IT WORKS</span><h2>From information<br>to understanding.</h2></div><p>A focused workflow that keeps the source of every useful answer within reach.</p></div>
                    <div class="steps-grid">
                        <article class="step-card"><span class="step-number">01</span><div class="step-icon">↥</div><h3>Bring your knowledge</h3><p>Add text files and text-based PDFs to your private workspace. LogicStrand prepares them for search.</p></article>
                        <article class="step-card"><span class="step-number">02</span><div class="step-icon">⌕</div><h3>Ask what matters</h3><p>Put a question in plain language. Relevant passages are found before an answer is composed.</p></article>
                        <article class="step-card"><span class="step-number">03</span><div class="step-icon">↗</div><h3>Follow the evidence</h3><p>Read a concise answer alongside its source passages, then revisit it in your history.</p></article>
                    </div>
                </div>
            </section>

            <section id="principles" class="section site-container principle-section">
                <div class="principle-panel">
                    <div class="principle-symbol" aria-hidden="true">✳</div>
                    <div><span class="section-kicker light-kicker">03 / OUR APPROACH</span><h2>Confidence starts<br>with context.</h2><p>LogicStrand is designed to make sources visible and gaps obvious. When your documents cannot support an answer, the workspace says so. The passages selected for a question are sent to Groq to generate the response.</p><a href="{{ route('register') }}" class="button button-light">Create your workspace <span aria-hidden="true">↗</span></a></div>
                </div>
            </section>

            <section class="closing-section site-container">
                <span class="section-kicker">A BETTER THREAD TO FOLLOW</span>
                <h2>Turn what you know into<br><em>what you can use.</em></h2>
                <a href="{{ route('register') }}" class="button button-blue">Get started with LogicStrand <span aria-hidden="true">↗</span></a>
            </section>
        </main>

        <footer class="site-footer"><div class="site-container footer-inner"><a href="{{ route('home') }}" class="site-brand"><x-app-logo-icon class="brand-mark" /><span>LogicStrand<span class="brand-period">.</span></span></a><span>Knowledge, connected with clarity.</span><span>© {{ date('Y') }} LogicStrand</span></div></footer>
    </div>
</body>
</html>
