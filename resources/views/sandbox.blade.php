<x-layouts::app :title="'Sandbox'">
    <div class="page-shell workspace-page sandbox-page">
        <header class="workspace-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / SANDBOX</span><h1>A place to find the thread.</h1><p>Try the path from source to question to a reviewable answer.</p></div><a href="{{ route('pricing') }}" class="workspace-heading-link">Explore plans <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></header>
        <section class="workspace-spotlight sandbox-spotlight">
            <div class="workspace-spotlight-copy">
                <span class="workspace-kicker"><i class="fa-solid fa-flask" aria-hidden="true"></i> {{ $access?->isActive() ? 'YOUR SEVEN DAY SANDBOX' : ($access ? 'YOUR ACCESS HAS ENDED' : 'YOUR NEXT STEP') }}</span>
                <h2>{{ $access?->isActive() ? 'Your seven days of clarity start here.' : ($access ? 'Ready for your next chapter?' : 'Choose a plan to begin exploring.') }}</h2>
                <p>{{ $access?->isActive() ? 'Start with a sample source, or bring one of your own. Every question is a chance to see the evidence more clearly.' : ($access ? 'Your sources and answers are still here. Choose a new period to keep exploring.' : 'Start a seven day Sandbox trial, then follow your first question back to the source.') }}</p>
                @if($access?->isActive())<span class="sandbox-days"><strong>{{ $access->daysRemaining() }}</strong> days remaining · through {{ $access->access_ends_at->format('M j, Y') }}</span>@else<a href="{{ $access ? route('pricing') : route('checkout.show', 'sandbox') }}" class="button button-light">{{ $access ? 'Explore plans' : 'Start Sandbox' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>@endif
            </div>
            <div class="workspace-strand" aria-hidden="true"><span></span><span></span><span></span><i class="fa-solid fa-circle-nodes"></i></div>
        </section>
        <section class="sandbox-progress" aria-label="Your progress">
            <div><span>01 / SOURCE</span><strong>{{ $documentCount }} documents</strong><small>{{ $readyCount }} ready to explore</small></div>
            <div><span>02 / QUESTION</span><strong>{{ $answerCount }} answers</strong><small>Saved in your history</small></div>
            <div><span>03 / ACCESS</span><strong>{{ $access?->isActive() ? $access->daysRemaining().' days left' : 'Paused' }}</strong><small>{{ $access?->isActive() ? 'In your Sandbox period' : 'Your work remains available' }}</small></div>
        </section>
        <div class="sandbox-section-head"><span class="page-eyebrow">THE FIRST THREE MOVES</span><h2>From source to insight.</h2><p>Follow the sequence, or jump straight to the step you need.</p></div>
        <section class="sandbox-steps">
            <article class="sandbox-step"><span class="sandbox-step-number">01 / SOURCE</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></div><h3>Begin with a source.</h3><p>Use our starter renewal policy, or add a text file or text-based PDF from your own library.</p>
                @if($access?->isActive() && ! $hasSample)
                    <form method="POST" action="{{ route('sandbox.source') }}">@csrf<button type="submit" class="sandbox-step-link">Add starter source <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></form>
                @elseif($hasSample)
                    <a href="{{ route('documents.index') }}" class="sandbox-step-link">Starter source added <i class="fa-solid fa-check" aria-hidden="true"></i></a>
                @else
                    <a href="{{ route('pricing') }}" class="sandbox-step-link">Choose a plan <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @endif
            </article>
            <article class="sandbox-step"><span class="sandbox-step-number">02 / QUESTION</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></div><h3>Ask what matters.</h3><p>Try a practical question: “What approvals are required before renewal?”</p><a href="{{ $access?->isActive() && $readyCount ? route('answers.create', ['question' => 'What approvals are required before renewal?']) : ($access?->isActive() ? route('documents.index') : route('pricing')) }}" class="sandbox-step-link">{{ $readyCount ? 'Ask this question' : 'Add a source first' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
            <article class="sandbox-step"><span class="sandbox-step-number">03 / EVIDENCE</span><div class="sandbox-step-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></div><h3>Follow the evidence.</h3><p>Open an answer and inspect the exact passages it cites. Your answers stay in your history.</p><a href="{{ route('answers.index') }}" class="sandbox-step-link">View answer history <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
        </section>
        <p class="sandbox-note">The starter source is illustrative. Questions about your ready documents use Groq, and relevant passages are sent to generate the response.</p>
    </div>
</x-layouts::app>
