<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAnswerUsage extends Model
{
    protected $fillable = ['user_id', 'usage_date', 'answer_count'];

    protected function casts(): array
    {
        return ['usage_date' => 'date', 'answer_count' => 'integer'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
