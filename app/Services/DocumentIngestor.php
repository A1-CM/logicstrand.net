<?php

namespace App\Services;

use App\Models\KnowledgeDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser;

class DocumentIngestor
{
    public function ingest(KnowledgeDocument $document): void
    {
        $path = Storage::disk('local')->path($document->path);

        if ($document->mime_type === 'application/pdf') {
            $pdf = (new Parser)->parseFile($path);
            $pages = [];

            foreach ($pdf->getPages() as $index => $page) {
                $pages[] = ['number' => $index + 1, 'text' => $page->getText()];
            }
        } else {
            $text = file_get_contents($path);

            if ($text === false || ! mb_check_encoding($text, 'UTF-8')) {
                throw new RuntimeException('Text files must contain readable UTF-8 text.');
            }

            $pages = [['number' => null, 'text' => $text]];
        }

        $passages = [];

        foreach ($pages as $page) {
            foreach ($this->split($page['text']) as $body) {
                $passages[] = ['page' => $page['number'], 'body' => $body];
            }
        }

        if ($passages === []) {
            throw new RuntimeException('No readable text found. Scanned PDFs are not supported yet.');
        }

        $document->update(['processing_stage' => 'indexing', 'stage_updated_at' => now()]);

        DB::transaction(function () use ($document, $passages): void {
            $document->chunks()->delete();

            foreach ($passages as $position => $passage) {
                $document->chunks()->create([
                    'page' => $passage['page'],
                    'position' => $position,
                    'body' => $passage['body'],
                ]);

            }

            $document->update(['status' => 'ready', 'processing_stage' => 'ready', 'stage_updated_at' => now(), 'error' => null]);
        });
    }

    /** @return list<string> */
    private function split(string $text): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($text === '') {
            return [];
        }

        $words = preg_split('/\s+/u', $text) ?: [];
        $passages = [];

        for ($start = 0; $start < count($words); $start += 130) {
            $body = implode(' ', array_slice($words, $start, 160));

            if ($body !== '') {
                $passages[] = $body;
            }
        }

        return $passages;
    }
}
