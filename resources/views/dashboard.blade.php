<x-layouts::app :title="'Overview'">
    <div class="page-shell">
        <div class="page-heading">
            <div><span class="page-eyebrow">YOUR WORKSPACE / OVERVIEW</span><h1>Good to see you, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Your knowledge is ready when you are.</p></div>
            <a href="{{ route('answers.create') }}" class="button button-blue">Ask a question <span aria-hidden="true">↗</span></a>
        </div>
        <section class="plan-status-card">
            <div class="plan-status-icon">✳</div>
            <div class="plan-status-copy">
                <span class="page-eyebrow">YOUR ACCESS</span>
                @if($access?->isActive())
                    <h2>{{ config('plans.'.$access->plan.'.name') }} is ready.</h2>
                    <p>{{ $access->daysRemaining() }} days remaining in this period · Access through {{ $access->access_ends_at->format('M j, Y') }}.</p>
                @elseif($access)
                    <h2>Your access period has ended.</h2>
                    <p>Your saved work is still here. Choose a new period to keep exploring your documents.</p>
                @else
                    <h2>Start with seven days in Sandbox.</h2>
                    <p>Choose a plan to add sources, ask questions, and trace each answer to its evidence.</p>
                @endif
            </div>
            <a href="{{ $access?->isActive() ? route('sandbox.index') : route('pricing') }}" class="plan-status-link">{{ $access?->isActive() ? 'Open Sandbox' : 'Explore plans' }} <span aria-hidden="true">↗</span></a>
        </section>
        <div class="dashboard-hero">
            <div><span class="dashboard-hero-kicker">WORK WITH CLARITY</span><h2>One place for the answers<br>behind your next move.</h2><p>Add sources, ask questions, and see the evidence that connects them.</p><div class="hero-mini-actions"><a href="{{ route('documents.index') }}">Add a document <span>↗</span></a><a href="{{ route('answers.create') }}">Explore a question <span>↗</span></a></div></div>
            <div class="dashboard-art" aria-hidden="true"><span class="art-line a"></span><span class="art-line b"></span><span class="art-line c"></span><i class="art-node n1"></i><i class="art-node n2"></i><i class="art-node n3"></i></div>
        </div>
        <div class="metric-grid">
            <div class="metric-card"><span>Ready documents</span><strong>{{ $readyDocuments }}</strong><small>Sources you can ask about</small></div>
            <div class="metric-card"><span>Processing</span><strong>{{ $processingDocuments }}</strong><small>Files being prepared</small></div>
            <div class="metric-card"><span>Saved answers</span><strong>{{ $answerCount }}</strong><small>Questions explored so far</small></div>
        </div>
        <div class="dashboard-columns">
            <section class="content-card"><div class="card-heading"><div><span class="page-eyebrow">RECENT ACTIVITY</span><h2>Questions & answers</h2></div><a href="{{ route('answers.index') }}">View all ↗</a></div>
                @forelse($recentAnswers as $answer)
                    <a href="{{ route('answers.show', $answer) }}" class="list-row"><span class="row-icon">?</span><span class="row-main"><strong>{{ $answer->question }}</strong><small>{{ $answer->created_at->diffForHumans() }}</small></span><span class="row-arrow">↗</span></a>
                @empty
                    <div class="empty-small"><strong>No questions yet</strong><p>Your saved answers will appear here.</p><a href="{{ route('answers.create') }}">Ask your first question ↗</a></div>
                @endforelse
            </section>
            <section class="content-card"><div class="card-heading"><div><span class="page-eyebrow">YOUR LIBRARY</span><h2>Recent documents</h2></div><a href="{{ route('documents.index') }}">View all ↗</a></div>
                @forelse($recentDocuments as $document)
                    <div class="list-row"><span class="row-icon">≡</span><span class="row-main"><strong>{{ $document->name }}</strong><small>{{ ucfirst($document->status) }} · {{ $document->created_at->diffForHumans() }}</small></span></div>
                @empty
                    <div class="empty-small"><strong>Your library starts here</strong><p>Upload a document to ground your first answer.</p><a href="{{ route('documents.index') }}">Add a document ↗</a></div>
                @endforelse
            </section>
        </div>
    </div>
</x-layouts::app>
