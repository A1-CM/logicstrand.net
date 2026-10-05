<x-layouts::app :title="'Sandbox'">
    <div class="page-shell sandbox-page">
        <div class="page-heading">
            <div><span class="page-eyebrow">YOUR WORKSPACE / SANDBOX</span><h1>A place to find the thread.</h1><p>Try the path from source to question to a reviewable answer.</p></div>
            <a href="{{ route('pricing') }}" class="button button-dark">Explore plans <span aria-hidden="true">↗</span></a>
        </div>

        <section class="sandbox-hero">
            <div class="sandbox-hero-copy">
                <span class="dashboard-hero-kicker">{{ $access?->isActive() ? 'YOUR ACCESS IS ACTIVE' : ($access ? 'YOUR ACCESS HAS ENDED' : 'YOUR NEXT STEP') }}</span>
                <h2>{{ $access?->isActive() ? ($access->plan === 'sandbox' ? 'Your seven days of clarity start here.' : 'Your workspace is ready to explore.') : ($access ? 'Ready for your next chapter?' : 'Choose a plan to begin exploring.') }}</h2>
                <p>{{ $access?->isActive() ? 'Start with a sample source, or bring one of your own. Every question is a chance to see the evidence more clearly.' : ($access ? 'Your sources and answers are still here. Choose a new period to keep exploring.' : 'Start a seven day Sandbox trial, then follow your first question back to the source.') }}</p>
                @if($access?->isActive())
                    <div class="sandbox-time"><strong>{{ $access->daysRemaining() }}</strong><span>days remaining<br>until {{ $access->access_ends_at->format('M j, Y') }}</span></div>
                @else
                    <a href="{{ $access ? route('pricing') : route('checkout.show', 'sandbox') }}" class="button button-light">{{ $access ? 'Explore plans' : 'Start Sandbox' }} <span aria-hidden="true">↗</span></a>
                @endif
            </div>
            <img src="{{ asset('images/strand-hero.webp') }}" alt="Cobalt strands weaving through paper" width="1400" height="933">
        </section>

        @if($access?->isActive())
            <section class="sandbox-progress" aria-label="Your progress">
                <div><span>01 / SOURCE</span><strong>{{ $documentCount }} documents</strong><small>{{ $readyCount }} ready to explore</small></div>
                <div><span>02 / QUESTION</span><strong>{{ $answerCount }} answers</strong><small>Saved in your history</small></div>
                <div><span>03 / PLAN</span><strong>{{ config('plans.'.$access->plan.'.name') }}</strong><small>{{ $access->daysRemaining() }} days left in this period</small></div>
            </section>
        @endif

        <div class="sandbox-section-head"><span class="page-eyebrow">THE FIRST THREE MOVES</span><h2>From source to insight.</h2><p>Follow the sequence, or jump straight to the step you need.</p></div>
        <section class="sandbox-steps">
            <article class="sandbox-step"><span class="sandbox-step-number">01</span><div class="sandbox-step-icon">≡</div><h3>Begin with a source.</h3><p>Use our starter renewal policy, or add a text file or text-based PDF from your own library.</p>
                @if($access?->isActive() && ! $hasSample)
                    <form method="POST" action="{{ route('sandbox.source') }}">@csrf<button type="submit" class="sandbox-step-link">Add starter source ↗</button></form>
                @elseif($hasSample)
                    <a href="{{ route('documents.index') }}" class="sandbox-step-link">Starter source added ✓</a>
                @else
                    <a href="{{ route('pricing') }}" class="sandbox-step-link">Choose a plan ↗</a>
                @endif
            </article>
            <article class="sandbox-step"><span class="sandbox-step-number">02</span><div class="sandbox-step-icon">?</div><h3>Ask what matters.</h3><p>Try a practical question: “What approvals are required before renewal?”</p><a href="{{ $access?->isActive() ? route('answers.create', ['question' => 'What approvals are required before renewal?']) : route('pricing') }}" class="sandbox-step-link">Ask this question ↗</a></article>
            <article class="sandbox-step"><span class="sandbox-step-number">03</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></div><h3>Follow the evidence.</h3><p>Open an answer and inspect the exact passages it cites. Your answers stay in your history.</p><a href="{{ route('answers.index') }}" class="sandbox-step-link">View answer history ↗</a></article>
        </section>
        <p class="sandbox-note">The starter source is illustrative. Questions about your ready documents use Groq, and relevant passages are sent to generate the response.</p>
    </div>
</x-layouts::app>
