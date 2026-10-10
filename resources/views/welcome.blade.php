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
                <img class="brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899">
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="#platform">Platform</a>
                <a href="#use-cases">Who it helps</a>
                <a href="#how-it-works">How it works</a>
                <a href="#voices">Perspectives</a>
                <a href="{{ route('pricing') }}">Pricing</a>
                <a href="#contact">Contact</a>
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
                    <img class="hero-photo" src="{{ asset('images/logicstrand/hero.webp') }}" alt="" width="1774" height="1680" fetchpriority="high">
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
                    <figure class="platform-photo"><img src="{{ asset('images/logicstrand/platform-approach.webp') }}" alt="A focused workspace with a document library open on a monitor" width="1096" height="905" loading="lazy"><figcaption>THE WORK BEHIND THE ANSWER</figcaption></figure>
                </div>
                <div class="preview-card">
                    <div class="preview-top"><span class="preview-logo"><img src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899"></span><span class="preview-label">ANSWER PREVIEW</span></div>
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
                        <span class="usecase-index">01 / OPERATIONS</span><img class="usecase-image" src="{{ asset('images/logicstrand/operations-process.webp') }}" alt="Colleagues reviewing process documents at a desk" width="1055" height="461" loading="lazy">
                        <h3>Find the process behind the decision.</h3>
                        <p>Bring process notes and internal guides into one place. Ask where a step is documented and see the passage behind the answer.</p>
                        <div class="usecase-prompt"><span>TRY ASKING</span><strong>“What approvals are required before renewal?”</strong></div>
                    </article>
                    <article class="usecase-card">
                        <span class="usecase-index">02 / POLICY</span><img class="usecase-image" src="{{ asset('images/logicstrand/policy-review.webp') }}" alt="A person reviewing policy pages beside a laptop" width="1055" height="461" loading="lazy">
                        <h3>Keep policy context close.</h3>
                        <p>Explore dense policy documents in plain language, with source excerpts you can inspect before taking action.</p>
                        <div class="usecase-prompt"><span>TRY ASKING</span><strong>“Where is this requirement defined?”</strong></div>
                    </article>
                    <article class="usecase-card">
                        <span class="usecase-index">03 / RESEARCH</span><img class="usecase-image" src="{{ asset('images/logicstrand/research-review.webp') }}" alt="Research notes and a laptop arranged for review" width="1055" height="461" loading="lazy">
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
                    <div class="workflow-photo"><img src="{{ asset('images/logicstrand/workflow-team.webp') }}" alt="A team working together around documents and a laptop" width="3812" height="999" loading="lazy"><div><span>FROM SOURCE TO SENSE</span><strong>Connected knowledge,<br>made useful.</strong></div></div>
                    <div class="steps-grid">
                        <article class="step-card"><span class="step-number">01</span><div class="step-icon" aria-hidden="true"><img class="step-illustration" src="{{ asset('images/logicstrand/step-sources.webp') }}" alt="" width="1562" height="1562" loading="lazy"></div><h3>Bring your knowledge</h3><p>Add text files and text-based PDFs to your private workspace. LogicStrand prepares them for search.</p></article>
                        <article class="step-card"><span class="step-number">02</span><div class="step-icon" aria-hidden="true"><img class="step-illustration" src="{{ asset('images/logicstrand/step-question.webp') }}" alt="" width="1562" height="1562" loading="lazy"></div><h3>Ask what matters</h3><p>Put a question in plain language. Relevant passages are found before an answer is composed.</p></article>
                        <article class="step-card"><span class="step-number">03</span><div class="step-icon" aria-hidden="true"><img class="step-illustration" src="{{ asset('images/logicstrand/step-evidence.webp') }}" alt="" width="1562" height="1562" loading="lazy"></div><h3>Follow the evidence</h3><p>Read a concise answer alongside its source passages, then revisit it in your history.</p></article>
                    </div>
                </div>
            </section>

            <section id="evidence" class="section site-container evidence-section">
                <div class="evidence-heading">
                    <span class="section-kicker">04 / THE EVIDENCE PATH</span>
                    <h2>A line of sight<br>from question to source.</h2>
                    <p>Smart tech should make it easier to inspect the information behind an answer.</p>
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
                    <div class="voices-feature"><img src="{{ asset('images/logicstrand/human-perspective.webp') }}" alt="A professional reflecting while reviewing material on a tablet" width="1352" height="1689" loading="lazy"><span>ONE THREAD / MANY WAYS FORWARD</span></div>
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

            <section id="contact" class="section site-container contact-section" aria-labelledby="contact-title">
                <div class="contact-info">
                    <span class="section-kicker">08 / GET IN TOUCH</span>
                    <h2 id="contact-title">Let's start<br><em>a conversation.</em></h2>
                    <p>Questions about LogicStrand, a source-backed workflow, or where to begin? Tell us what you have in mind.</p>
                    <a class="contact-email" href="mailto:support@logicstrand.net">
                        <span class="contact-email-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                        <span><small>EMAIL OUR TEAM</small><strong>support@logicstrand.net</strong></span>
                    </a>
                    <div class="contact-offices">
                        <div class="contact-office">
                            <span class="contact-office-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                            <div><div class="contact-office-heading"><span class="contact-office-label">SRI LANKA</span><a class="contact-map-link" href="https://www.google.com/maps/search/?api=1&query=No%2018%20Lakeview%20Crescent%20Rajagiriya%20Sri%20Lanka" target="_blank" rel="noopener noreferrer" aria-label="View Sri Lanka office on Google Maps"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Map</a></div><address>No. 18, Lakeview Crescent<br>Rajagiriya, Sri Lanka</address><a href="tel:+94770001201"><i class="fa-solid fa-phone" aria-hidden="true"></i> +94 77 000 1201</a></div>
                        </div>
                        <div class="contact-office">
                            <span class="contact-office-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                            <div><div class="contact-office-heading"><span class="contact-office-label">UNITED STATES</span><a class="contact-map-link" href="https://www.google.com/maps/search/?api=1&query=455%20Market%20Street%20San%20Francisco%20CA%2094105%20USA" target="_blank" rel="noopener noreferrer" aria-label="View United States office on Google Maps"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Map</a></div><address>455 Market Street<br>San Francisco, CA 94105, USA</address><a href="tel:+14155550101"><i class="fa-solid fa-phone" aria-hidden="true"></i> +1 415-555-0101</a></div>
                        </div>
                    </div>
                    <p class="contact-legal"><strong>LogicStrand</strong> was founded on 18 September 2023.<br>Sri Lanka: LogicStrand Technologies (Pvt) Ltd<br>United States: LogicStrand Technologies Inc.</p>
                </div>
                <div class="contact-panel">
                    <div class="contact-panel-heading">
                        <span>DIRECT LINE / CONTACT</span>
                        <h3>Send us a note.</h3>
                        <p>Share a little context and our team will get back to you by email.</p>
                    </div>
                    <div class="contact-panel-grid">
                        <div class="contact-form-wrap">
                            @if(filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key')))
                                <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
                                    @csrf
                                    <div class="contact-honeypot" aria-hidden="true"><label for="contact-website">Leave this field empty</label><input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>
                                    @error('delivery')<p class="contact-form-alert" role="alert">{{ $message }}</p>@enderror
                                    @if($errors->any() && ! $errors->has('delivery'))<p class="contact-form-alert" role="alert">Please check the highlighted fields and try again.</p>@endif
                                    <div class="contact-field"><label for="contact-name">Your name <span aria-hidden="true">*</span></label><input id="contact-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">@error('name')<small class="contact-field-error">{{ $message }}</small>@enderror</div>
                                    <div class="contact-field"><label for="contact-email">Email address <span aria-hidden="true">*</span></label><input id="contact-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">@error('email')<small class="contact-field-error">{{ $message }}</small>@enderror</div>
                                    <div class="contact-field"><label for="contact-organization">Organization <small>Optional</small></label><input id="contact-organization" name="organization" type="text" value="{{ old('organization') }}" autocomplete="organization" maxlength="120" aria-invalid="{{ $errors->has('organization') ? 'true' : 'false' }}">@error('organization')<small class="contact-field-error">{{ $message }}</small>@enderror</div>
                                    <div class="contact-field"><label for="contact-subject">Subject <span aria-hidden="true">*</span></label><input id="contact-subject" name="subject" type="text" value="{{ old('subject') }}" maxlength="150" required aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}">@error('subject')<small class="contact-field-error">{{ $message }}</small>@enderror</div>
                                    <div class="contact-field"><label for="contact-message">Message <span aria-hidden="true">*</span></label><textarea id="contact-message" name="message" rows="5" minlength="10" maxlength="5000" required aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}">{{ old('message') }}</textarea>@error('message')<small class="contact-field-error">{{ $message }}</small>@enderror</div>
                                    <div class="contact-turnstile"><div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="contact" data-size="flexible" data-theme="light"></div>@error('turnstile')<small class="contact-field-error">{{ $message }}</small>@enderror</div>
                                    <button class="button button-blue contact-submit" type="submit">Send message</button>
                                </form>
                            @else
                                <div class="contact-unavailable" role="status"><i class="fa-solid fa-envelope" aria-hidden="true"></i><strong>The form is temporarily unavailable.</strong><p>You can still reach us at <a href="mailto:support@logicstrand.net">support@logicstrand.net</a>.</p></div>
                            @endif
                        </div>
                        <aside class="contact-social" aria-label="LogicStrand social profiles">
                            <span class="contact-social-kicker">FOLLOW US ON</span>
                            <div class="contact-social-links">
                                <a href="https://medium.com/@LogicStrand" target="_blank" rel="noopener noreferrer" aria-label="Follow LogicStrand on Medium" data-tooltip="Medium"><i class="fa-brands fa-medium" aria-hidden="true"></i></a>
                                <a href="https://www.youtube.com/@LogicStrand-d4t" target="_blank" rel="noopener noreferrer" aria-label="Follow LogicStrand on YouTube" data-tooltip="YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
                                <a href="https://www.facebook.com/LogicStrand/" target="_blank" rel="noopener noreferrer" aria-label="Follow LogicStrand on Facebook" data-tooltip="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            <section class="closing-section site-container">
                <span class="section-kicker">A BETTER THREAD TO FOLLOW</span>
                <h2>Your next answer<br>should have a <em>source.</em></h2>
                <a href="{{ route('pricing') }}" class="button button-blue">Get started with LogicStrand <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
            </section>
        </main>

        @include('partials.site-footer')
    </div>
    @if(filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key')))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    @include('partials.cookie-consent')
    <x-toast-stack />
</body>
</html>
