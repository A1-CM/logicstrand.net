<?php

namespace App\Jobs;

use App\Models\KnowledgeDocument;
use App\Services\DocumentIngestor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $disk = Storage::disk('local');
        $disk->makeDirectory('document-locks');
        $lock = fopen($disk->path('document-locks/'.$this->documentId.'.lock'), 'c');

        if ($lock === false) {
            throw new RuntimeException('Unable to lock document processing.');
        }

        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);

            return;
        }

        try {
            $document = KnowledgeDocument::find($this->documentId);

            if (! $document || $document->status !== 'processing') {
                return;
            }

            $document->update([
                'processing_stage' => 'extracting',
                'processing_started_at' => $document->processing_started_at ?? now(),
                'stage_updated_at' => now(),
            ]);

            try {
                $ingestor->ingest($document);
            } catch (Throwable $exception) {
                Log::warning('Document ingestion failed', [
                    'document_id' => $document->id,
                    'exception' => $exception,
                ]);

                $document->update([
                    'status' => 'failed',
                    'processing_stage' => 'failed',
                    'stage_updated_at' => now(),
                    'error' => $exception instanceof RuntimeException
                        ? $exception->getMessage()
                        : 'We could not read this file. Try a text-based PDF or UTF-8 text file.',
                ]);
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
