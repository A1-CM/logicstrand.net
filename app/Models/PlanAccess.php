<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $user_id
 * @property string $plan
 * @property Carbon|null $trial_started_at
 * @property Carbon|null $trial_ends_at
 * @property Carbon $access_ends_at
 * @property string $card_last_four
 */
class PlanAccess extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'trial_started_at',
        'trial_ends_at',
        'access_ends_at',
        'card_last_four',
    ];

    protected function casts(): array
    {
        return [
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'access_ends_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->access_ends_at->isFuture();
    }

    public function daysRemaining(): int
    {
        return max(0, (int) ceil(now()->diffInSeconds($this->access_ends_at, false) / 86400));
    }
}
