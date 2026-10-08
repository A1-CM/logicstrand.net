<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    protected $fillable = ['user_id', 'question', 'answer', 'is_favorite', 'private_note', 'viewed_at'];

    protected function casts(): array
    {
        return ['is_favorite' => 'boolean', 'viewed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<AnswerCitation, $this> */
    public function citations(): HasMany
    {
        return $this->hasMany(AnswerCitation::class);
    }
}
