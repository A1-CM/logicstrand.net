<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $page
 * @property string $body
 */
class DocumentChunk extends Model
{
    protected $fillable = ['knowledge_document_id', 'page', 'position', 'body'];

    /** @return BelongsTo<KnowledgeDocument, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(KnowledgeDocument::class, 'knowledge_document_id');
    }

    /** @return HasMany<AnswerCitation, $this> */
    public function citations(): HasMany
    {
        return $this->hasMany(AnswerCitation::class);
    }
}
