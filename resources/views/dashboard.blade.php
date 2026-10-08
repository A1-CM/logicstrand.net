<x-layouts::app :title="'Overview'">
    <div class="page-shell workspace-page">
        <header class="workspace-heading">
            <div><span class="page-eyebrow">YOUR WORKSPACE / OVERVIEW</span><h1>Good to see you, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Pick up where you left off, with your sources close by.</p></div>
            <span class="workspace-date">{{ now()->format('l, F j') }}</span>
        </header>

        @php
            $active = $access?->isActive();
            $nextTitle = ! $active ? ($access ? 'Your access period has ended.' : 'Start with seven days in Sandbox.') : ($failedDocuments > 0 ? 'A document needs your attention.' : ($processingDocuments > 0 && $readyDocuments === 0 ? 'Your source is being prepared.' : ($readyDocuments === 0 ? 'Begin with a source.' : 'Your sources are ready.')));
            $nextBody = ! $active ? 'Your saved work is still here. Choose a plan to keep exploring.' : ($failedDocuments > 0 ? 'Review the extraction issue and retry your file.' : ($processingDocuments > 0 && $readyDocuments === 0 ? 'We will show it in your library as soon as extraction finishes.' : ($readyDocuments === 0 ? 'Add a text file or text-based PDF to ground your first answer.' : 'Ask a question and inspect the passages behind the answer.')));
            $nextRoute = ! $active ? route('pricing') : ($readyDocuments > 0 && $failedDocuments === 0 ? route('answers.create') : route('documents.index'));
            $nextLabel = ! $active ? 'Explore plans' : ($readyDocuments > 0 && $failedDocuments === 0 ? 'Ask a question' : 'Open documents');
        @endphp
        <section class="workspace-spotlight" aria-labelledby="next-step-heading">
            <div class="workspace-spotlight-copy">
                <span class="workspace-kicker"><i class="fa-solid fa-bolt" aria-hidden="true"></i> YOUR NEXT STEP</span>
                <h2 id="next-step-heading">{{ $nextTitle }}</h2>
                <p>{{ $nextBody }}</p>
                <a href="{{ $nextRoute }}" class="button button-light">{{ $nextLabel }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="workspace-strand" aria-hidden="true"><span></span><span></span><span></span><i class="fa-solid fa-circle-nodes"></i></div>
        </section>

        @if($showOnboarding)
            <section class="workspace-onboarding content-card" aria-labelledby="setup-checklist-title">
                <div class="card-heading"><div><span class="page-eyebrow">A SHORT START</span><h2 id="setup-checklist-title">Your first three steps</h2><p>Try a source, ask a sample question, then inspect the evidence.</p></div><form method="POST" action="{{ route('onboarding.dismiss') }}">@csrf<button class="row-action" type="submit">Dismiss checklist</button></form></div>
                <ol class="onboarding-steps">
                    <li @class(['is-complete' => $onboardingSteps['source']])><span>01</span><div><strong>Add the starter source</strong><small>{{ $onboardingSteps['source'] ? 'Starter source is ready' : 'Upload the included renewal policy' }}</small></div>@if($onboardingSteps['source'])<i class="fa-solid fa-circle-check" aria-label="Complete"></i>@else<form method="POST" action="{{ route('sandbox.source') }}">@csrf<button class="row-action" type="submit" @disabled(! $active)>Add sample source</button></form>@endif</li>
                    <li @class(['is-complete' => $onboardingSteps['question']])><span>02</span><div><strong>Ask the sample question</strong><small>“What approvals are required before renewal?”</small></div>@if($onboardingSteps['question'])<i class="fa-solid fa-circle-check" aria-label="Complete"></i>@else<a class="row-action" href="{{ $active ? route('answers.create', ['question' => 'What approvals are required before renewal?']) : route('pricing') }}">Ask sample</a>@endif</li>
                    <li @class(['is-complete' => $onboardingSteps['review']])><span>03</span><div><strong>Review the answer</strong><small>Open it and follow each cited passage</small></div>@if($onboardingSteps['review'])<i class="fa-solid fa-circle-check" aria-label="Complete"></i>@elseif($sampleAnswer)<a class="row-action" href="{{ route('answers.show', $sampleAnswer) }}">Review answer</a>@else<span class="onboarding-pending">Complete step 2 first</span>@endif</li>
                </ol>
            </section>
        @endif

        <div class="workspace-summary">
            <section class="workspace-plan-card">
                <span class="page-eyebrow">CURRENT ACCESS</span>
                <h2>{{ $access ? config('plans.'.$access->plan.'.name') : 'No plan yet' }}</h2>
                <p>@if($active) @if($access->period_started_at) Period {{ $access->period_started_at->format('M j, Y') }}–{{ $access->access_ends_at->format('M j, Y') }} · @endif {{ $access->daysRemaining() }} days remaining · Access through {{ $access->access_ends_at->format('M j, Y') }}. @elseif($access) @if($access->period_started_at) Period {{ $access->period_started_at->format('M j, Y') }}–{{ $access->access_ends_at->format('M j, Y') }} · @endif Access ended {{ $access->access_ends_at->format('M j, Y') }}. @else Choose a plan to start your workspace. @endif</p>
                <a href="{{ $access?->plan === 'sandbox' ? route('sandbox.index') : route('pricing') }}">{{ $access?->plan === 'sandbox' ? 'Open Sandbox guide' : 'View plans' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </section>
            <div class="workspace-metrics" aria-label="Workspace usage">
                <div><span><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Documents</span><strong>{{ $documentCount }} <small>/ {{ $documentLimit }}</small></strong><div class="usage-track" role="progressbar" aria-label="Document use" aria-valuemin="0" aria-valuemax="{{ $documentLimit }}" aria-valuenow="{{ min($documentCount, $documentLimit) }}"><span style="width: {{ $documentLimit ? min(100, $documentCount / $documentLimit * 100) : 0 }}%"></span></div><p>{{ $readyDocuments }} ready · {{ $processingDocuments }} processing @if($failedDocuments) · {{ $failedDocuments }} failed @endif</p></div>
                <div><span><i class="fa-solid fa-comments" aria-hidden="true"></i> Saved answers today</span><strong>{{ $usedToday }} <small>/ {{ $dailyLimit }}</small></strong><div class="usage-track" role="progressbar" aria-label="Saved answers today" aria-valuemin="0" aria-valuemax="{{ $dailyLimit }}" aria-valuenow="{{ min($usedToday, $dailyLimit) }}"><span style="width: {{ $dailyLimit ? min(100, $usedToday / $dailyLimit * 100) : 0 }}%"></span></div><p>{{ max(0, $dailyLimit - $usedToday) }} available today · {{ $answerCount }} total</p></div>
            </div>
        </div>

        <section class="workspace-path" aria-label="How to use your workspace">
            <div class="workspace-path-heading"><span class="page-eyebrow">THE PATH TO AN ANSWER</span><h2>From material to meaning.</h2></div>
            <a href="{{ route('documents.index') }}"><span>01</span><i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i><strong>Add a source</strong><small>{{ $documentCount ? $documentCount.' in your library' : 'PDF or text' }}</small></a>
            <a href="{{ route('documents.index', ['status' => 'processing']) }}"><span>02</span><i class="fa-solid fa-spinner" aria-hidden="true"></i><strong>Make it ready</strong><small>{{ $processingDocuments ? $processingDocuments.' processing' : $readyDocuments.' ready to search' }}</small></a>
            <a href="{{ $active ? route('answers.create') : route('pricing') }}"><span>03</span><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i><strong>Ask and review</strong><small>Trace every cited passage</small></a>
        </section>

        <div class="dashboard-columns workspace-activity">
            <section class="content-card"><div class="card-heading"><div><span class="page-eyebrow">RECENT ACTIVITY</span><h2>Questions & answers</h2></div><a href="{{ route('answers.index') }}">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                @forelse($recentAnswers as $answer)
                    <a href="{{ route('answers.show', $answer) }}" class="list-row"><span class="row-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span><span class="row-main"><strong>{{ $answer->question }}</strong><small>{{ $answer->created_at->diffForHumans() }}</small></span><span class="row-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span></a>
                @empty
                    <div class="empty-small"><strong>No questions yet</strong><p>Your saved answers will appear here.</p><a href="{{ $active ? route('answers.create') : route('pricing') }}">Start exploring <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                @endforelse
            </section>
            <section class="content-card"><div class="card-heading"><div><span class="page-eyebrow">YOUR LIBRARY</span><h2>Recent documents</h2></div><a href="{{ route('documents.index') }}">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                @forelse($recentDocuments as $document)
                    <a href="{{ route('documents.index', ['status' => $document->status]) }}" class="list-row"><span class="row-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></span><span class="row-main"><strong>{{ $document->name }}</strong><small>{{ ucfirst($document->status) }} · {{ $document->created_at->diffForHumans() }}</small></span><span class="row-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span></a>
                @empty
                    <div class="empty-small"><strong>Your library starts here</strong><p>Upload a document to ground your first answer.</p><a href="{{ route('documents.index') }}">Add a document <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                @endforelse
            </section>
        </div>
    </div>
</x-layouts::app>
