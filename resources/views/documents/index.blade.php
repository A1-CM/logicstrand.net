<x-layouts::app :title="'Documents'">
    <div class="page-shell">
        <div class="page-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / LIBRARY</span><h1>Documents</h1><p>The sources behind your answers, kept in your private workspace.</p></div><span class="count-pill">{{ $documents->count() }} / {{ $limit }} files</span></div>
        <div class="upload-card">
            <div><span class="page-eyebrow">ADD KNOWLEDGE</span><h2>Bring a source into focus.</h2><p>Upload a UTF-8 text file or text-based PDF, up to 10 MB. Scanned PDFs need OCR and are not supported yet.</p></div>
            @if(auth()->user()->planAccess?->isActive())
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="upload-form">
                @csrf
                <label for="document" class="file-drop"><span class="file-icon" aria-hidden="true"><i class="fa-solid fa-file-arrow-up"></i></span><strong>Choose a document</strong><span>PDF or TXT · maximum 10 MB</span><input id="document" type="file" name="document" accept=".pdf,.txt,application/pdf,text/plain" required></label>
                @error('document') <p class="field-error">{{ $message }}</p> @enderror
                <button type="submit" class="button button-blue">Upload document <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></button>
            </form>
            @else
                <div class="upload-form"><p>Choose a plan to add more sources to your library.</p><a href="{{ route('pricing') }}" class="button button-blue">Explore plans <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></a></div>
            @endif
        </div>
        <p class="privacy-note"><span>✳</span> When you ask a question, relevant passages from your documents are sent to Groq to compose the answer.</p>
        <section class="content-card library-card"><div class="card-heading"><div><span class="page-eyebrow">SOURCES</span><h2>Your library</h2></div></div>
            @forelse($documents as $document)
                <div class="document-row"><span class="row-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></span><div class="row-main"><strong>{{ $document->name }}</strong><small>{{ number_format($document->size / 1024, 1) }} KB · Added {{ $document->created_at->format('M j, Y') }}</small>@if($document->error)<small class="field-error">{{ $document->error }}</small>@endif</div><span class="status-pill status-{{ $document->status }}">{{ ucfirst($document->status) }}</span><a href="{{ route('documents.download', $document) }}" class="row-action">Download</a><form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Remove this document and answers that cite it?')">@csrf @method('DELETE')<button type="submit" class="row-action danger">Remove</button></form></div>
            @empty
                <div class="empty-small"><strong>No documents yet</strong><p>Add a source above to start building your knowledge library.</p></div>
            @endforelse
        </section>
    </div>
</x-layouts::app>
