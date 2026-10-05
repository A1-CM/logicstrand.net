<x-layouts::app :title="'Answer history'">
    <div class="page-shell">
        <div class="page-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / HISTORY</span><h1>Answer history</h1><p>Return to questions you have explored and the sources behind them.</p></div><a href="{{ route('answers.create') }}" class="button button-blue">Ask a question <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a></div>
        <div class="content-card history-card">
            @forelse($answers as $answer)
                <a href="{{ route('answers.show', $answer) }}" class="history-row"><span class="row-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span><span><strong>{{ $answer->question }}</strong><small>{{ Str::limit($answer->answer, 160) }}</small></span><time>{{ $answer->created_at->format('M j, Y') }}</time><span class="row-arrow"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a>
            @empty
                <div class="empty-small"><strong>Your story starts with a question.</strong><p>Ask something about your documents and the answer will appear here.</p><a href="{{ route('answers.create') }}">Ask a question <i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></a></div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $answers->links() }}</div>
    </div>
</x-layouts::app>
