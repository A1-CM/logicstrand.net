<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $name
 * @property string $processing_stage
 * @property string $status
 * @property Carbon|null $processing_started_at
 * @property Carbon|null $stage_updated_at
 */
class KnowledgeDocument extends Model
{
    protected $fillable = ['user_id', 'name', 'path', 'mime_type', 'size', 'status', 'processing_stage', 'processing_started_at', 'stage_updated_at', 'error'];

    protected function casts(): array
    {
        return ['processing_started_at' => 'datetime', 'stage_updated_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<DocumentChunk, $this> */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }
}
