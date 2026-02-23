<?php

namespace App\Models\Central;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель подписки tenant
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $plan_id
 * @property string|null $stripe_id
 * @property string|null $stripe_status
 * @property string|null $stripe_price
 * @property int|null $quantity
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon|null $ends_at
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property SubscriptionStatus|string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Subscription extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status', // active, cancelled, expired, trial
        'starts_at',
        'ends_at',
        'cancelled_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Проверить, активна ли подписка
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE->value
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Проверить, находится ли подписка на пробном периоде
     *
     * @return bool
     */
    public function isTrial(): bool
    {
        return $this->status === SubscriptionStatus::TRIALING->value
            && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }
}
