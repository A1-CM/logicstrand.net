<?php

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentSearch
{
    /** @return Collection<int, DocumentChunk> */
    public function search(int $userId, string $question): Collection
    {
        preg_match_all('/[\p{L}\p{N}]{3,}/u', mb_strtolower($question), $matches);
        $stop = ['the', 'and', 'for', 'are', 'what', 'which', 'who', 'how', 'why', 'does', 'can', 'could', 'would', 'should', 'this', 'that', 'from', 'with', 'about', 'have'];
        $terms = array_slice(array_values(array_unique(array_diff($matches[0], $stop))), 0, 8);

        if ($terms === []) {
            return collect();
        }

        $query = implode(' OR ', array_map(fn (string $term) => '"'.$term.'"', $terms));
        $ids = DB::table('document_chunks_fts')
            ->join('document_chunks', 'document_chunks.id', '=', 'document_chunks_fts.rowid')
            ->join('knowledge_documents', 'knowledge_documents.id', '=', 'document_chunks.knowledge_document_id')
            ->where('knowledge_documents.user_id', $userId)
            ->where('knowledge_documents.status', 'ready')
            ->whereRaw('document_chunks_fts MATCH ?', [$query])
            ->orderByRaw('bm25(document_chunks_fts)')
            ->limit(6)
            ->pluck('document_chunks.id');

        $chunks = DocumentChunk::with('document')->whereIn('id', $ids)->get()->keyBy('id');

        return $ids->map(fn ($id) => $chunks->get($id))->filter()->values();
    }
}
