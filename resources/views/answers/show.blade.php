<x-layouts::app :title="'Answer'">
    <div class="page-shell narrow-page">
        <a href="{{ route('answers.index') }}" class="back-link">← &nbsp; Back to history</a>
        <div class="page-heading answer-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / ANSWER</span><h1>{{ $answer->question }}</h1><p>Asked {{ $answer->created_at->format('F j, Y \a\t g:i A') }}</p></div></div>
        <article class="answer-card"><div class="answer-card-top"><span class="answer-badge">✳ &nbsp; LOGICSTRAND ANSWER</span><span>{{ $answer->citations->count() }} {{ Str::plural('source', $answer->citations->count()) }}</span></div><div class="answer-body">{{ $answer->answer }}</div></article>
        <section class="source-section"><div class="card-heading"><div><span class="page-eyebrow">FOLLOW THE EVIDENCE</span><h2>Source passages</h2></div></div>
            @forelse($answer->citations as $citation)
                <article class="source-card"><div class="source-card-top"><span class="source-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><strong>{{ $citation->chunk->document->name }}</strong><small>{{ $citation->chunk->page ? 'Page '.$citation->chunk->page : 'Text document' }}</small></div><a href="{{ route('documents.download', $citation->chunk->document) }}">Download ↗</a></div><p>{{ $citation->chunk->body }}</p></article>
            @empty
                <div class="empty-small"><strong>No supporting passage found.</strong><p>Try a more specific question or add another document.</p></div>
            @endforelse
        </section>
        <a href="{{ route('answers.create') }}" class="button button-dark">Ask another question <span aria-hidden="true">↗</span></a>
    </div>
</x-layouts::app>
