<?php

namespace App\Jobs;

use App\Models\KnowledgeDocument;
use App\Services\DocumentIngestor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessDocument implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(public int $documentId) {}

    public function handle(DocumentIngestor $ingestor): void
    {
        $document = KnowledgeDocument::find($this->documentId);

        if (! $document || $document->status !== 'processing') {
            return;
        }

        try {
            $ingestor->ingest($document);
        } catch (Throwable $exception) {
            Log::warning('Document ingestion failed', [
                'document_id' => $document->id,
                'exception' => $exception,
            ]);

            $document->update([
                'status' => 'failed',
                'error' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'We could not read this file. Try a text-based PDF or UTF-8 text file.',
            ]);
        }
    }
}
