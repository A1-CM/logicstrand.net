<x-layouts::app :title="'Ask a question'">
    <div class="page-shell workspace-page">
        <header class="workspace-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / ASK</span><h1>Ask what matters.</h1><p>Turn your sources into an answer you can check.</p></div><a href="{{ route('documents.index') }}" class="workspace-heading-link">View documents <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></header>
        <div class="ask-layout">
            <section class="ask-panel workspace-ask-panel">
                <span class="workspace-kicker"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> SOURCE-BACKED ANSWERS</span>
                <h2>What would you like to understand?</h2>
                <p>LogicStrand searches your ready documents, then asks Groq to answer using relevant passages. Answers without enough evidence say so.</p>
                @if($readyCount === 0)
                    <div class="workspace-state-card" role="status"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i><div><strong>No ready documents yet</strong><p>{{ $processingCount > 0 ? 'Your upload is processing. Come back when it shows Ready.' : 'Add a text file or text-based PDF before asking a question.' }}</p><a href="{{ route('documents.index') }}">Open documents <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></div>
                @elseif($remaining === 0)
                    <div class="workspace-state-card" role="status"><i class="fa-solid fa-clock" aria-hidden="true"></i><div><strong>Today's saved-answer allowance is full</strong><p>You can explore your previous answers now and ask again tomorrow.</p><a href="{{ route('answers.index') }}">Open answer history <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></div>
                @elseif(! $serviceConfigured)
                    <div class="workspace-state-card" role="status"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><div><strong>Answer service is unavailable</strong><p>The workspace needs a Groq API key before it can generate source-backed answers. Your documents and saved answers remain available.</p><a href="{{ route('answers.index') }}">Open answer history <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></div>
                @else
                    <form method="POST" action="{{ route('answers.store') }}" class="question-form" data-busy-form>
                        @csrf
                        <label for="question">Your question</label>
                        <textarea name="question" id="question" rows="6" maxlength="1000" placeholder="For example: What approvals are needed before renewal?" required>{{ old('question', request()->query('question')) }}</textarea>
                        @error('question') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                        <div class="form-footer"><span>Only your own ready documents are searched.</span><button type="submit" class="button button-blue" data-busy-label="Finding evidence…">Find an answer <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div>
                    </form>
                @endif
            </section>
            <aside class="ask-context">
                <div class="ask-context-card"><span class="page-eyebrow">YOUR SOURCES</span><strong>{{ $readyCount }}</strong><p>ready {{ Str::plural('document', $readyCount) }} to search</p><a href="{{ route('documents.index') }}">Manage library <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                <div class="ask-context-card"><span class="page-eyebrow">TODAY'S ALLOWANCE</span><strong>{{ $remaining }}</strong><p>saved {{ Str::plural('answer', $remaining) }} available of {{ $limit }}</p><small>Only saved answers count. Failed service requests do not.</small></div>
                @if($readyCount > 0 && $remaining > 0)<div class="ask-prompts"><span class="page-eyebrow">NEED A STARTING POINT?</span><button type="button" data-question-example="Summarize the main requirements in my documents.">Summarize the main requirements <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button><button type="button" data-question-example="What needs approval before this process can continue?">Find approval steps <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div>@endif
            </aside>
        </div>
    </div>
</x-layouts::app>
