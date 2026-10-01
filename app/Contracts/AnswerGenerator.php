<?php

namespace App\Contracts;

use App\Models\DocumentChunk;
use Illuminate\Support\Collection;

interface AnswerGenerator
{
    /** @param Collection<int, DocumentChunk> $chunks
     * @return array{answer: string, citation_ids: list<int>}
     */
    public function generate(string $question, Collection $chunks): array;
}
