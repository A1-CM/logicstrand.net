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
                <a href="#use-cases">Who it helps</a>
                <a href="#how-it-works">How it works</a>
                <a href="#voices">Perspectives</a>
                <a href="{{ route('pricing') }}">Pricing</a>
            </nav>
            <div class="site-header-actions">
                @auth
                    <a class="site-login" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="site-login" href="{{ route('login') }}">Sign in</a>
                    <a class="button button-dark button-small" href="{{ route('pricing') }}">Get started <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
                @endauth
            </div>
        </header>

        <main>
            <section class="hero site-container">
                <div class="hero-copy">
                    <div class="eyebrow"><span class="eyebrow-dot"></span> INTELLIGENCE, WITH A LINE OF SIGHT</div>
                    <h1>Make every answer <em>traceable.</em></h1>
                    <p class="hero-lede">For the people making sense of policies, processes, and research: bring your documents together, ask the important questions, and see where each answer comes from.</p>
                    <div class="hero-actions">
                        <a href="{{ route('pricing') }}" class="button button-blue">Start your workspace <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
                        <a href="#how-it-works" class="text-link">See how it works <span aria-hidden="true">↓</span></a>
                    </div>
                    <div class="hero-note"><span class="note-line"></span> Your documents. Your questions. A clearer way forward.</div>
                </div>
                <div class="hero-visual" aria-label="Illustration of connected knowledge strands">
                    <img class="hero-photo" src="{{ asset('images/strand-hero.webp') }}" alt="" width="1400" height="933" fetchpriority="high">
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
                    <p>Important knowledge lives across files, policies, and notes. LogicStrand gives it a more useful shape: a private space to explore questions and keep the evidence close.</p>
                    <figure class="platform-photo"><img src="{{ asset('images/strand-worktable.webp') }}" alt="An open binder and blue strand across a knowledge workspace" width="1400" height="933" loading="lazy"><figcaption>THE WORK BEHIND THE ANSWER</figcaption></figure>
                </div>
                <div class="preview-card">
                    <div class="preview-top"><span class="preview-logo"><x-app-logo-icon class="preview-icon" /> LogicStrand</span><span class="preview-label">ANSWER PREVIEW</span></div>
                    <div class="preview-question"><span class="preview-question-label">YOUR QUESTION</span><strong>What should we know before updating our renewal process?</strong></div>
                    <div class="preview-answer"><span class="preview-spark">✳</span><div><span class="preview-answer-label">A CLEARER ANSWER</span><p>Start with the documented approval steps and timing requirements. The sources below show the relevant passages, so you can review the basis for the answer.</p></div></div>
                    <div class="preview-sources"><span>↳ &nbsp; Example policy · p. 2</span><span>↳ &nbsp; Process notes · p. 4</span></div>
                    <div class="preview-foot">Illustrative example — your workspace uses your own documents.</div>
                </div>
            </section>

            <section id="use-cases" class="section site-container usecase-section">
                <div class="usecase-intro">
                    <div><span class="section-kicker">02 / BUILT FOR THE WORK BEHIND THE WORK</span><h2>When the details matter,<br><em>follow the thread.</em></h2></div>
                    <p>From a single policy to a growing library, LogicStrand helps knowledge-heavy work move from searching to understanding.</p>
                </div>
                <div class="usecase-grid">
                    <article class="usecase-card">
                        <span class="usecase-index">01 / OPERATIONS</span><img class="usecase-image" src="{{ asset('images/strand-worktable.webp') }}" alt="Open process binder on a sunlit desk" width="1400" height="933" loading="lazy">
                        <h3>Find the process behind the decision.</h3>
                        <p>Bring process notes and internal guides into one place. Ask where a step is documented and see the passage behind the answer.</p>
                        <div class="usecase-prompt"><span>TRY ASKING</span><strong>“What approvals are required before renewal?”</strong></div>
                    </article>
                    <article class="usecase-card">
                        <span class="usecase-index">02 / POLICY</span><img class="usecase-image" src="{{ asset('images/strand-policy.webp') }}" alt="Marked policy pages with a cobalt strand" width="1100" height="733" loading="lazy">
                        <h3>Keep policy context close.</h3>
                        <p>Explore dense policy documents in plain language, with source excerpts you can inspect before taking action.</p>
                        <div class="usecase-prompt"><span>TRY ASKING</span><strong>“Where is this requirement defined?”</strong></div>
                    </article>
                    <article class="usecase-card">
                        <span class="usecase-index">03 / RESEARCH</span><img class="usecase-image" src="{{ asset('images/strand-research.webp') }}" alt="Layered research folios connected by blue lines" width="1100" height="733" loading="lazy">
                        <h3>Turn reading into a clearer view.</h3>
                        <p>Ask across your research notes and reports. Keep useful answers together with the evidence that informed them.</p>
                        <div class="usecase-prompt"><span>TRY ASKING</span><strong>“Which findings support this direction?”</strong></div>
                    </article>
                </div>
                <div class="usecase-outro"><span class="usecase-outro-mark" aria-hidden="true"><i class="fa-solid fa-eye"></i></span><p><strong>Your own lens, your own sources.</strong> LogicStrand works with the documents you choose to add.</p><a href="{{ route('pricing') }}">Start exploring <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a></div>
            </section>

            <section id="how-it-works" class="section workflow-section">
                <div class="site-container">
                    <div class="workflow-heading"><div><span class="section-kicker">03 / HOW IT WORKS</span><h2>From information<br>to understanding.</h2></div><p>A focused workflow that keeps the source of every useful answer within reach.</p></div>
                    <div class="workflow-photo"><img src="{{ asset('images/strand-library.webp') }}" alt="A blue strand connecting two libraries of source material" width="1400" height="933" loading="lazy"><div><span>FROM SOURCE TO SENSE</span><strong>Connected knowledge,<br>made useful.</strong></div></div>
                    <div class="steps-grid">
                        <article class="step-card"><span class="step-number">01</span><div class="step-icon" aria-hidden="true"><i class="fa-solid fa-file-arrow-up"></i></div><h3>Bring your knowledge</h3><p>Add text files and text-based PDFs to your private workspace. LogicStrand prepares them for search.</p></article>
                        <article class="step-card"><span class="step-number">02</span><div class="step-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></div><h3>Ask what matters</h3><p>Put a question in plain language. Relevant passages are found before an answer is composed.</p></article>
                        <article class="step-card"><span class="step-number">03</span><div class="step-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></div><h3>Follow the evidence</h3><p>Read a concise answer alongside its source passages, then revisit it in your history.</p></article>
                    </div>
                </div>
            </section>

            <section id="evidence" class="section site-container evidence-section">
                <div class="evidence-heading">
                    <span class="section-kicker">04 / THE EVIDENCE PATH</span>
                    <h2>A line of sight<br>from question to source.</h2>
                    <p>Useful AI should make it easier to inspect the information behind a response. LogicStrand keeps the connection visible.</p>
                </div>
                <div class="evidence-map">
                    <div class="evidence-map-grid" aria-hidden="true"></div>
                    <div class="evidence-map-header">
                        <span><i class="fa-solid fa-circle-nodes" aria-hidden="true"></i> THE EVIDENCE PATH</span>
                        <span>AN EXAMPLE, STEP BY STEP</span>
                    </div>
                    <div class="evidence-map-line" aria-hidden="true"></div>
                    <div class="evidence-step evidence-source">
                        <div class="evidence-step-heading"><span class="evidence-step-index">01</span><div><span>YOUR MATERIAL</span><strong>Start with your sources.</strong></div></div>
                        <div class="evidence-file"><span class="evidence-file-icon" aria-hidden="true"><i class="fa-solid fa-file-pdf"></i></span><div><strong>Renewal policy.pdf</strong><small>Page 4 · approval process</small></div><span class="evidence-file-type">PDF</span></div>
                        <div class="evidence-file"><span class="evidence-file-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></span><div><strong>Process notes.txt</strong><small>Internal reference</small></div><span class="evidence-file-type">TXT</span></div>
                        <p class="evidence-step-caption">Your documents become a searchable library.</p>
                    </div>
                    <div class="evidence-step evidence-question">
                        <div class="evidence-step-heading"><span class="evidence-step-index">02</span><div><span>YOUR QUESTION</span><strong>Ask what matters.</strong></div></div>
                        <div class="evidence-question-bubble"><span>QUESTION</span><p>What needs approval before a renewal?</p></div>
                        <div class="evidence-signal"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Relevant passages selected</span></div>
                    </div>
                    <div class="evidence-step evidence-result">
                        <div class="evidence-step-heading"><span class="evidence-step-index">03</span><div><span>REVIEWABLE ANSWER</span><strong>See the connection.</strong></div></div>
                        <div class="evidence-result-card">
                            <span class="evidence-result-label"><i class="fa-solid fa-circle-nodes" aria-hidden="true"></i> LOGICSTRAND ANSWER</span>
                            <p>The documented process requires two approvals before renewal.</p>
                            <div class="evidence-citation"><i class="fa-solid fa-link" aria-hidden="true"></i><span>Renewal policy <small>p. 4 · approval process</small></span></div>
                        </div>
                    </div>
                    <div class="evidence-map-foot">Illustrative workflow · your workspace uses your own documents</div>
                </div>
                <div class="evidence-benefits"><div><span>01</span><strong>Ask in plain language.</strong><p>Start with the question you actually have.</p></div><div><span>02</span><strong>Inspect the passage.</strong><p>Review where the answer came from.</p></div><div><span>03</span><strong>Know when to look deeper.</strong><p>See when the documents lack enough evidence.</p></div></div>
            </section>

            <section id="voices" class="section site-container voices-section">
                <div class="voices-heading"><div><span class="section-kicker">05 / THE HUMAN SIDE OF CLARITY</span><h2>The questions behind<br><em>the work.</em></h2></div><p>Illustrative perspectives from the kinds of work LogicStrand is built to support.</p></div>
                <div class="voices-grid">
                    <div class="voices-feature"><img src="{{ asset('images/strand-voices.webp') }}" alt="A blue thread making its way through a paper maze" width="1122" height="1402" loading="lazy"><span>ONE THREAD / MANY WAYS FORWARD</span></div>
                    <div class="voices-quotes">
                        <blockquote class="voices-primary"><span class="voices-mark">“</span><p>I need the answer, and the exact paragraph that supports it.</p><footer><span>01</span> Operations perspective</footer></blockquote>
                        <div class="voices-small-grid">
                            <blockquote><p>“A short answer helps. Seeing where it came from helps me act.”</p><footer>Policy perspective</footer></blockquote>
                            <blockquote><p>“The useful part is knowing which source deserves a closer look.”</p><footer>Research perspective</footer></blockquote>
                        </div>
                    </div>
                </div>
            </section>

            <section id="principles" class="section site-container principle-section">
                <div class="principle-panel">
                    <div class="principle-symbol" aria-hidden="true">✳</div>
                    <div><span class="section-kicker light-kicker">06 / OUR APPROACH</span><h2>Confidence starts<br>with context.</h2><p>LogicStrand is designed to make sources visible and gaps obvious. When your documents cannot support an answer, the workspace says so. The passages selected for a question are sent to Groq to generate the response.</p><a href="{{ route('pricing') }}" class="button button-light">Create your workspace <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a></div>
                </div>
            </section>

            <section id="faq" class="section site-container faq-section">
                <div class="faq-intro"><span class="section-kicker">07 / GOOD QUESTIONS</span><h2>A little more<br><em>clarity.</em></h2><p>Before you begin, here is how LogicStrand handles the most important parts of the workflow.</p><a href="{{ route('pricing') }}" class="text-link">Create a workspace <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a></div>
                <div class="faq-list">
                    <details><summary><span>01</span> What can I add to my workspace?<b aria-hidden="true">+</b></summary><p>Upload UTF-8 text files and text-based PDFs, up to 10 MB each. Scanned PDFs need OCR and are not supported in this version.</p></details>
                    <details><summary><span>02</span> How does an answer connect to a source?<b aria-hidden="true">+</b></summary><p>LogicStrand searches your ready documents, sends relevant passages to Groq, and shows the passages cited by the answer so you can review them.</p></details>
                    <details><summary><span>03</span> What if my documents do not have the answer?<b aria-hidden="true">+</b></summary><p>The workspace says it could not find enough evidence. You can refine the question or add another source.</p></details>
                    <details><summary><span>04</span> Who can see my documents?<b aria-hidden="true">+</b></summary><p>Your account has a private workspace. The passages selected for a question are sent to Groq to compose the answer.</p></details>
                </div>
            </section>

            <section class="closing-section site-container">
                <span class="section-kicker">A BETTER THREAD TO FOLLOW</span>
                <h2>Your next answer<br>should have a <em>source.</em></h2>
                <a href="{{ route('pricing') }}" class="button button-blue">Get started with LogicStrand <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
            </section>
        </main>

        <footer class="site-footer"><div class="site-container footer-inner"><a href="{{ route('home') }}" class="site-brand"><x-app-logo-icon class="brand-mark" /><span>LogicStrand<span class="brand-period">.</span></span></a><span>Knowledge, connected with clarity.</span><span>© {{ date('Y') }} LogicStrand</span></div></footer>
    </div>
    <x-toast-stack />
</body>
</html>
