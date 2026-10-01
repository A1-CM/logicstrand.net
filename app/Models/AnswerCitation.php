<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerCitation extends Model
{
    public $timestamps = false;

    protected $fillable = ['answer_id', 'document_chunk_id'];

    /** @return BelongsTo<DocumentChunk, $this> */
    public function chunk(): BelongsTo
    {
        return $this->belongsTo(DocumentChunk::class, 'document_chunk_id');
    }
}
