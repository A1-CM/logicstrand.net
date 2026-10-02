<x-layouts::app :title="'Ask a question'">
    <div class="page-shell narrow-page">
        <div class="page-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / ASK</span><h1>Ask what matters.</h1><p>A good question is the beginning of a clearer answer.</p></div></div>
        <div class="ask-panel">
            <div class="ask-symbol">✳</div>
            <span class="page-eyebrow">SOURCE-BACKED ANSWERS</span>
            <h2>What would you like to understand?</h2>
            <p>LogicStrand searches your ready documents, then asks Groq to answer using relevant passages. Answers without enough evidence say so.</p>
            <form method="POST" action="{{ route('answers.store') }}" class="question-form">
                @csrf
                <label for="question">Your question</label>
                <textarea name="question" id="question" rows="5" maxlength="1000" placeholder="For example: What changed in the latest policy?" required>{{ old('question', request()->query('question')) }}</textarea>
                @error('question') <p class="field-error">{{ $message }}</p> @enderror
                <div class="form-footer"><span>Only your own ready documents are searched.</span><button type="submit" class="button button-blue">Find an answer <span aria-hidden="true">↗</span></button></div>
            </form>
        </div>
        <div class="ask-tip"><span>↗</span><p><strong>New here?</strong> <a href="{{ route('documents.index') }}">Add a document</a> first so your answers can point back to a source.</p></div>
    </div>
</x-layouts::app>
