<x-layouts::app :title="'Documents'">
    <div class="page-shell workspace-page" data-document-page>
        <header class="workspace-heading"><div><span class="page-eyebrow">YOUR WORKSPACE / DOCUMENTS</span><h1>Documents</h1><p>Keep the material behind every answer organized and ready.</p></div><span class="count-pill">{{ $counts['all'] }} / {{ $limit }} files</span></header>
        <div class="workspace-two-column">
            <section class="upload-card workspace-upload">
                <div><span class="page-eyebrow">ADD KNOWLEDGE</span><h2>Bring a source into focus.</h2><p>Upload a UTF-8 text file or text-based PDF, up to 10 MB. Scanned PDFs need OCR and are not supported yet.</p><div class="upload-fineprint"><i class="fa-solid fa-lock" aria-hidden="true"></i> Originals stay private in your workspace.</div></div>
                @if(auth()->user()->planAccess?->isActive() && $counts['all'] < $limit)
                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="upload-form" data-busy-form data-async-upload>
                        @csrf
                        <label for="document" class="file-drop"><span class="file-icon" aria-hidden="true"><i class="fa-solid fa-file-arrow-up"></i></span><strong>Choose a document</strong><span>PDF or TXT · maximum 10 MB</span><input id="document" type="file" name="document" accept=".pdf,.txt,application/pdf,text/plain" data-file-input required><small data-file-name aria-live="polite">No file selected</small></label>
                        @error('document') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                        <button type="submit" class="button button-blue" data-busy-label="Uploading…">Upload document <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                    </form>
                @else
                    <div class="upload-unavailable">
                        <p>{{ ! auth()->user()->planAccess?->isActive() ? 'Choose a plan to add more sources to your library.' : 'Your library is full. Remove a document to make room for another.' }}</p>
                        @if(! auth()->user()->planAccess?->isActive())<a href="{{ route('pricing') }}" class="button button-blue">Explore plans</a>@endif
                    </div>
                @endif
            </section>
            <aside class="workspace-help-card"><span class="page-eyebrow">HOW IT WORKS</span><ol><li><b>01</b><span>Upload your source</span></li><li><b>02</b><span>Wait for Ready status</span></li><li><b>03</b><span>Ask a question</span></li></ol><p>When you ask, relevant passages from your documents are sent to Groq to compose the answer.</p></aside>
        </div>
        <section class="content-card library-card workspace-library">
            <div class="card-heading"><div><span class="page-eyebrow">SOURCE LIBRARY</span><h2>Your documents</h2></div>@if($counts['processing'] > 0)<span class="processing-note" role="status"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> {{ $counts['processing'] }} processing · live status</span>@endif</div>
            <nav class="workspace-tabs" aria-label="Filter documents">
                @foreach(['all' => 'All', 'ready' => 'Ready', 'processing' => 'Processing', 'failed' => 'Failed'] as $value => $label)
                    <a href="{{ route('documents.index', ['status' => $value]) }}" @class(['is-active' => $status === $value]) @if($status === $value) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$value] }}</span></a>
                @endforeach
            </nav>
            @forelse($documents as $document)
                @php($progress = $progressDocuments[$document->id] ?? null)
                <div class="document-row" @if($document->status === 'processing' && $progress) data-document-progress data-status-url="{{ $progress['statusUrl'] }}" data-process-url="{{ $progress['processUrl'] }}" data-stage="{{ $progress['stage'] }}" data-auto-start="{{ $progress['autoStart'] ? 'true' : 'false' }}" data-started-at="{{ $progress['startedAt'] ?? '' }}" @endif>
                    <span class="row-icon" aria-hidden="true"><i class="fa-solid {{ $document->mime_type === 'application/pdf' ? 'fa-file-pdf' : 'fa-file-lines' }}"></i></span>
                    <div class="row-main"><strong>{{ $document->name }}</strong><small>{{ number_format($document->size / 1024, 1) }} KB · Added {{ $document->created_at->format('M j, Y') }}</small>@if($document->error)<small class="field-error">{{ $document->error }}</small>@elseif($document->status === 'processing')<small class="document-progress-copy" data-progress-copy role="status">{{ ucfirst($document->processing_stage ?: 'queued') }} · checking status…</small><button type="button" class="row-action" data-resume-document hidden>Resume processing</button>@endif</div>
                    <span class="status-pill status-{{ $document->status }}">{{ ucfirst($document->status) }}</span>
                    <div class="document-actions"><a href="{{ route('documents.download', $document) }}" class="row-action">Download</a>
                        @if($document->status === 'failed' && auth()->user()->planAccess?->isActive() && ! str_contains((string) $document->error, 'Scanned PDFs'))<form method="POST" action="{{ route('documents.retry', $document) }}">@csrf<button type="submit" class="row-action">Retry</button></form>@endif
                        <form method="POST" action="{{ route('documents.destroy', $document) }}" data-confirm-delete data-document-name="{{ $document->name }}">@csrf @method('DELETE')<button type="submit" class="row-action danger">Remove</button></form>
                    </div>
                </div>
            @empty
                <div class="empty-small"><strong>{{ $status === 'all' ? 'No documents yet' : 'No '.$status.' documents' }}</strong><p>{{ $status === 'all' ? 'Add a source above to start building your knowledge library.' : 'Try another filter to see the rest of your library.' }}</p></div>
            @endforelse
            @if($documents->hasPages())<div class="pagination-wrap">{{ $documents->links() }}</div>@endif
        </section>
    </div>
</x-layouts::app>
