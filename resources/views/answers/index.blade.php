<x-layouts::app :title="'Answer history'">
    <div class="page-shell workspace-page">
        <header class="workspace-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / HISTORY</span><h1>Answer history</h1><p>Return to a question and follow its evidence again.</p></div><a href="{{ auth()->user()->planAccess?->isActive() ? route('answers.create') : route('pricing') }}" class="button button-blue">Ask a question <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></header>
        <section class="content-card workspace-history">
            <form method="GET" action="{{ route('answers.index') }}" class="history-filters" role="search">
                <label for="history-search" class="sr-only">Search questions and answers</label><div class="history-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="history-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Search questions and answers"></div>
                <label for="history-status" class="sr-only">Filter by evidence status</label><select id="history-status" name="status"><option value="all" @selected($status === 'all')>All answers</option><option value="cited" @selected($status === 'cited')>With sources</option><option value="insufficient" @selected($status === 'insufficient')>Without sources</option><option value="favorite" @selected($status === 'favorite')>Favorites</option></select>
                <button type="submit" class="button button-dark">Search</button>
                @if($search !== '' || $status !== 'all')<a href="{{ route('answers.index') }}" class="history-clear">Clear</a>@endif
            </form>
            <div class="workspace-history-head"><span>QUESTION</span><span>WHEN</span></div>
            @forelse($answers as $answer)
                <a href="{{ route('answers.show', $answer) }}" class="history-row"><span class="row-icon" aria-hidden="true"><i class="fa-solid {{ $answer->is_favorite ? 'fa-star' : 'fa-comment-dots' }}"></i></span><span><strong>{{ $answer->question }}</strong><small>{{ Str::limit($answer->answer, 160) }}</small></span><time datetime="{{ $answer->created_at->toDateString() }}">{{ $answer->created_at->format('M j, Y') }}</time><span class="row-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span></a>
            @empty
                <div class="empty-small"><strong>{{ $search !== '' || $status !== 'all' ? 'No answers match your filters.' : 'Your story starts with a question.' }}</strong><p>{{ $search !== '' || $status !== 'all' ? 'Try another search or clear the filters.' : 'Ask something about your documents and the answer will appear here.' }}</p><a href="{{ $search !== '' || $status !== 'all' ? route('answers.index') : (auth()->user()->planAccess?->isActive() ? route('answers.create') : route('pricing')) }}">{{ $search !== '' || $status !== 'all' ? 'Clear filters' : 'Ask a question' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
            @endforelse
        </section>
        <div class="pagination-wrap">{{ $answers->links() }}</div>
    </div>
</x-layouts::app>
