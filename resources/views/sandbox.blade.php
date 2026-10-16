<x-layouts::app :title="'Sandbox'">
    <div class="page-shell workspace-page sandbox-page">
        <header class="workspace-heading">
            <div><span class="page-eyebrow">YOUR WORKSPACE / SANDBOX</span><h1>A place to find the thread.</h1><p>Try the path from source to question to a reviewable answer.</p></div>
            <a href="{{ route('pricing') }}" class="workspace-heading-link">Explore plans <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </header>

        <section class="workspace-spotlight sandbox-spotlight">
            <div class="workspace-spotlight-copy">
                <span class="workspace-kicker"><i class="fa-solid fa-flask" aria-hidden="true"></i> YOUR SEVEN DAY SANDBOX</span>
                <h2>Try a real question against a real source.</h2>
                <p>Start with the included renewal policy, ask what matters, and inspect the passages behind the answer. Your files and answers remain private to your account.</p>
                <span class="sandbox-days"><strong>{{ $access->daysRemaining() }}</strong> days remaining · through {{ $access->access_ends_at->format('M j, Y') }}</span>
            </div>
            <div class="workspace-strand" aria-hidden="true"><span></span><span></span><span></span><i class="fa-solid fa-circle-nodes"></i></div>
        </section>

        <section class="sandbox-progress" aria-label="Sandbox usage">
            <div><span>01 / SOURCE</span><strong>{{ $documentCount }} documents</strong><small>{{ $readyCount }} ready to search</small></div>
            <div><span>02 / ANSWERS</span><strong>{{ $answerCount }} saved</strong><small>{{ $remainingToday }} of {{ $questionLimit }} questions left today</small></div>
            <div><span>03 / ACCESS</span><strong>{{ $access->daysRemaining() }} days left</strong><small>Sandbox ends {{ $access->access_ends_at->format('M j, Y') }}</small></div>
        </section>

        <div class="sandbox-section-head"><span class="page-eyebrow">YOUR FIRST WORKFLOW</span><h2>From source to insight.</h2><p>Everything you need to try the core experience is here on this page.</p></div>
        <section class="sandbox-steps sandbox-workflow-steps">
            <article class="sandbox-step"><span class="sandbox-step-number">01 / SOURCE</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></div><h3>Begin with a source.</h3><p>An example renewal policy gives you a ready-to-search starting point.</p>
                @if($hasSample)
                    <span class="sandbox-step-link sandbox-source-ready"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Starter source ready</span>
                @else
                    <form method="POST" action="{{ route('sandbox.source') }}">@csrf<button type="submit" class="sandbox-step-link">Add starter source <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></form>
                @endif
            </article>
            <article class="sandbox-step"><span class="sandbox-step-number">02 / QUESTION</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></div><h3>Ask what matters.</h3><p>Try a practical question, then make it your own.</p>
                @if($readyCount > 0 && $remainingToday > 0)
                    <a href="#sandbox-question" class="sandbox-step-link">Ask your first question <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a>
                @elseif($readyCount === 0)
                    <span class="sandbox-step-link sandbox-step-muted">Add the starter source first</span>
                @else
                    <span class="sandbox-step-link sandbox-step-muted">Daily Sandbox limit reached</span>
                @endif
            </article>
            <article class="sandbox-step"><span class="sandbox-step-number">03 / EVIDENCE</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></div><h3>Follow the evidence.</h3><p>Each answer below shows the exact passages that support it.</p><a href="#sandbox-answers" class="sandbox-step-link">Review saved answers <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a></article>
        </section>

        @if($readyCount > 0)
            <section class="sandbox-question-card" id="sandbox-question" aria-labelledby="sandbox-question-title">
                <div class="sandbox-question-copy"><span class="page-eyebrow">ASK YOUR SOURCE</span><h2 id="sandbox-question-title">What would you like to understand?</h2><p>Questions use only passages from your ready documents. You have {{ $remainingToday }} saved {{ Str::plural('answer', $remainingToday) }} left today.</p></div>
                <form method="POST" action="{{ route('sandbox.ask') }}" class="sandbox-question-form" data-busy-form>
                    @csrf
                    <label for="sandbox-question-input">Your question</label>
                    <textarea id="sandbox-question-input" name="question" rows="3" maxlength="500" required placeholder="What approvals are required before renewal?">{{ old('question', 'What approvals are required before renewal?') }}</textarea>
                    @error('question')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    <button type="submit" class="button button-primary" @disabled($remainingToday < 1)>Ask LogicStrand <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                </form>
                <p class="sandbox-note">The answer uses the Base V.1 model and relevant passages from your documents. Those passages are sent to the configured AI service to generate your answer. It may say when the available sources do not contain enough evidence.</p>
            </section>
        @else
            <section class="sandbox-question-card sandbox-question-empty" id="sandbox-question">
                <span class="page-eyebrow">ASK YOUR SOURCE</span>
                <h2>{{ $readyCount === 0 ? 'Add the starter source to begin.' : 'You have used today’s Sandbox questions.' }}</h2>
                <p>{{ $readyCount === 0 ? 'The sample renewal policy is ready to add above. Once indexed, you can ask questions and inspect the evidence.' : 'Your saved answers stay available below. You can ask more questions tomorrow.' }}</p>
            </section>
        @endif

        <section class="sandbox-answer-section" id="sandbox-answers" aria-labelledby="sandbox-answers-title">
            <div class="card-heading"><div><span class="page-eyebrow">YOUR SANDBOX HISTORY</span><h2 id="sandbox-answers-title">Answers and evidence</h2><p>Review your latest saved answers and the passages used.</p></div><span class="count-pill">{{ $answerCount }} saved</span></div>
            @forelse($recentAnswers as $answer)
                <article class="sandbox-answer-card" id="answer-{{ $answer->id }}">
                    <div class="sandbox-answer-question"><span class="answer-badge"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> LOGICSTRAND ANSWER</span><small>{{ $answer->created_at->format('M j, Y · g:i A') }}</small><h3>{{ $answer->question }}</h3></div>
                    <p class="sandbox-answer-text">{{ $answer->answer }}</p>
                    <div class="sandbox-evidence-list">
                        @forelse($answer->citations as $citation)
                            <div class="sandbox-evidence"><span><i class="fa-solid fa-quote-left" aria-hidden="true"></i> {{ $citation->chunk->document->name }}@if($citation->chunk->page) · page {{ $citation->chunk->page }}@endif</span><p>{{ $citation->chunk->body }}</p></div>
                        @empty
                            <div class="sandbox-evidence sandbox-evidence-empty"><span><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Insufficient evidence</span><p>Try a narrower question or add more detail to your source material.</p></div>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="sandbox-answer-empty"><span class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-comments"></i></span><div><strong>Your first answer will appear here.</strong><p>Ask a question above to see its response and cited passages together.</p></div></div>
            @endforelse
        </section>

        <div class="sandbox-upgrade-strip"><div><span class="page-eyebrow">READY FOR MORE?</span><h2>Take your workflow further.</h2><p>Explore a plan to unlock your full document library, answer history, and workspace tools.</p></div><a href="{{ route('pricing') }}" class="button button-primary">Explore plans <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
    </div>
</x-layouts::app>
