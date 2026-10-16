<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'LS Atlas'])
    <meta name="description" content="Meet LS Atlas by LogicStrand: a private workspace for organizing enterprise knowledge and tracing answers back to their source documents.">
</head>
<body class="site-body">
    <div class="site-shell">
        <header class="site-header site-container">
            <a href="{{ route('home') }}" class="site-brand" aria-label="LogicStrand home"><img class="brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899"></a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="#capabilities">Capabilities</a>
                <a href="#workspace">Workspace</a>
                <a href="#roadmap">Roadmap</a>
                <a href="{{ route('pricing') }}">Pricing</a>
                <a href="{{ route('home') }}#contact">Contact</a>
            </nav>
            <div class="site-header-actions">
                @auth
                    <a class="site-login" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="button button-dark button-small" href="{{ route('register') }}">Create account <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right"></i></span></a>
                @endauth
            </div>
        </header>

        <main>
            <section class="atlas-hero site-container" id="overview">
                <div class="atlas-hero-copy">
                    <span class="section-kicker"><span class="atlas-live-dot"></span> INTRODUCING LS ATLAS BY LOGICSTRAND</span>
                    <h1>Knowledge you can <em>follow.</em></h1>
                    <p>LS Atlas gives teams a clear place to bring documents together, ask practical questions, and trace each answer to the passages that support it.</p>
                    <div class="atlas-hero-actions">
                        <a class="button button-blue" href="{{ route('register') }}">Create an account <i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></a>
                        <a class="button button-outline" href="{{ route('pricing') }}">View pricing <i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="atlas-hero-note"><i class="fa-solid fa-lock" aria-hidden="true"></i> A personal workspace for your documents and questions.</div>
                </div>
                <div class="atlas-hero-visual" aria-label="LS Atlas workspace preview showing a question with linked source evidence">
                    <img src="{{ asset('images/logicstrand/hero.webp') }}" alt="Abstract connected strands representing linked knowledge" width="1774" height="1680" fetchpriority="high">
                    <div class="atlas-preview-window">
                        <div class="atlas-window-bar"><span><i class="fa-solid fa-circle-nodes" aria-hidden="true"></i> LS ATLAS WORKSPACE</span><span class="atlas-window-status"><i></i> PERSONAL LIBRARY</span></div>
                        <div class="atlas-window-body">
                            <div class="atlas-window-label">YOUR QUESTION</div>
                            <strong>What needs approval before a renewal?</strong>
                            <div class="atlas-window-answer"><span class="atlas-answer-spark"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></span><p>The documented process calls for two approvals before renewal.</p></div>
                            <div class="atlas-window-label">SUPPORTING PASSAGES</div>
                            <div class="atlas-citation"><i class="fa-solid fa-file-lines" aria-hidden="true"></i><span><b>Renewal policy.pdf</b><small>Page 4 · approval process</small></span><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></div>
                            <div class="atlas-citation"><i class="fa-solid fa-file-lines" aria-hidden="true"></i><span><b>Process notes.txt</b><small>Internal reference</small></span><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></div>
                        </div>
                    </div>
                    <span class="atlas-visual-caption">A clearer path from question to evidence</span>
                </div>
            </section>

            <section class="atlas-proof-strip" aria-label="LS Atlas workflow">
                <div class="site-container atlas-proof-inner"><span><i class="fa-solid fa-folder-open" aria-hidden="true"></i> Organize sources</span><b></b><span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Ask in plain language</span><b></b><span><i class="fa-solid fa-link" aria-hidden="true"></i> Review the evidence</span></div>
            </section>

            <section class="atlas-capabilities section site-container" id="capabilities">
                <div class="atlas-section-heading"><span class="section-kicker">01 / THE WORKSPACE</span><h2>From scattered files<br>to <em>shared clarity.</em></h2><p>LS Atlas is built around the steps people already take when they need to understand policies, process notes, and research—not just retrieve a file.</p></div>
                <div class="atlas-capability-grid">
                    <article class="atlas-capability-card">
                        <div class="atlas-card-icon"><i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i></div><span class="atlas-card-index">01 / COLLECT</span><h3>Keep source material together</h3><p>Upload text files and text-based PDFs into a private document library. See what is ready, still processing, or needs attention.</p><a href="#workspace">See the document flow <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="atlas-capability-card atlas-capability-featured">
                        <div class="atlas-card-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></div><span class="atlas-card-index">02 / REASON</span><h3>Ask questions across your sources</h3><p>Ask in everyday language and get a concise response grounded in passages retrieved from your ready documents.</p><a href="#workspace">See how answers work <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="atlas-capability-card">
                        <div class="atlas-card-icon"><i class="fa-solid fa-bookmark" aria-hidden="true"></i></div><span class="atlas-card-index">03 / REVISIT</span><h3>Keep useful answers close</h3><p>Search answer history, save favorites, add private notes, and export answers with their cited source passages.</p><a href="#review">Explore answer review <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                </div>
            </section>

            <section class="atlas-workflow" id="workspace">
                <div class="site-container atlas-workflow-inner">
                    <div class="atlas-workflow-copy"><span class="section-kicker">02 / A REVIEWABLE WORKFLOW</span><h2>Every answer<br>leaves a <em>trail.</em></h2><p>As documents are prepared, LS Atlas makes their status visible. Ask a question when sources are ready, then open the cited passages and check the answer against the original material.</p>
                        <ol class="atlas-stage-list"><li><span>01</span><div><b>Add documents</b><small>Text files and text-based PDFs</small></div><i class="fa-solid fa-check" aria-hidden="true"></i></li><li><span>02</span><div><b>Prepare searchable passages</b><small>Follow extraction and indexing status</small></div><i class="fa-solid fa-check" aria-hidden="true"></i></li><li><span>03</span><div><b>Ask and inspect</b><small>Review cited passages and page numbers</small></div><i class="fa-solid fa-check" aria-hidden="true"></i></li></ol>
                    </div>
                    <div class="atlas-workflow-art"><img src="{{ asset('images/logicstrand/the-platform.webp') }}" alt="A quiet workspace with documents and a computer, representing focused knowledge work" width="1096" height="905" loading="lazy"><div class="atlas-art-callout"><span><i class="fa-solid fa-link" aria-hidden="true"></i> EVIDENCE IN VIEW</span><strong>Question → passage → source</strong></div></div>
                </div>
            </section>

            <section class="atlas-review section site-container" id="review">
                <div class="atlas-review-art"><img src="{{ asset('images/logicstrand/policy-review.webp') }}" alt="A professional reviewing a document at a desk" width="1096" height="905" loading="lazy"><span class="atlas-image-label"><i class="fa-solid fa-star" aria-hidden="true"></i> SAVED FOR LATER</span></div>
                <div class="atlas-review-copy"><span class="section-kicker">03 / BUILT FOR FOLLOW-THROUGH</span><h2>Good answers<br>should be <em>reusable.</em></h2><p>History keeps questions and answers easy to revisit. Save the ones that matter, attach a private reminder for your own context, or export a clean PDF with the evidence included.</p><div class="atlas-review-points"><span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search and filter history</span><span><i class="fa-solid fa-note-sticky" aria-hidden="true"></i> Add private notes</span><span><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Export cited answers</span></div></div>
            </section>

            <section class="atlas-roadmap" id="roadmap">
                <div class="site-container atlas-roadmap-inner">
                    <div class="atlas-roadmap-copy"><span class="section-kicker">NEXT PHASE / PRIVATE MODEL PATH</span><h2>Designed to grow<br>with your <em>knowledge.</em></h2><p>LS Atlas currently uses the Base V.1 model to generate answers from retrieved document passages. A future phase will explore custom-trained private language models and specialized models for knowledge bases and document management.</p><p class="atlas-roadmap-disclaimer">This is planned product direction. These private-model capabilities and NVIDIA integrations are not available in the current workspace.</p></div>
                    <div class="atlas-tech-panel"><div class="atlas-tech-panel-head"><span><i class="fa-solid fa-diagram-project" aria-hidden="true"></i> TECHNOLOGIES UNDER CONSIDERATION</span><small>FUTURE PHASE</small></div><div class="atlas-tech-list"><div><span class="atlas-tech-icon"><i class="fa-solid fa-brain" aria-hidden="true"></i></span><span><b>NeMo</b><small>Model development and customization</small></span></div><div><span class="atlas-tech-icon"><i class="fa-solid fa-server" aria-hidden="true"></i></span><span><b>NIM</b><small>Model inference microservices</small></span></div><div><span class="atlas-tech-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><span><b>NeMo Retriever</b><small>Knowledge retrieval workflows</small></span></div><div><span class="atlas-tech-icon"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i></span><span><b>Triton</b><small>Model serving direction</small></span></div></div><div class="atlas-tech-foot"><i class="fa-solid fa-code" aria-hidden="true"></i> SDKs and APIs will be introduced as this roadmap is validated.</div></div>
                </div>
            </section>

            <section class="atlas-final-cta site-container">
                <div><span class="section-kicker">START WITH WHAT YOU KNOW</span><h2>Give your documents<br>a clearer <em>way to answer.</em></h2></div>
                <div class="atlas-final-actions"><a href="{{ route('register') }}" class="button button-blue">Create an account <i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></a><a href="{{ route('pricing') }}" class="text-link">Compare plans <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
            </section>
        </main>
        @include('partials.site-footer')
    </div>
    @include('partials.cookie-consent')
    <x-toast-stack />
</body>
</html>
