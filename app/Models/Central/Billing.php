<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель биллинга tenant
 *
 * @property int $id
 * @property string $tenant_id
 * @property int|null $subscription_id
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string|null $payment_method
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon|null $due_date
 * @property string|null $invoice_number
 * @property array|null $metadata
 */
class Billing extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'amount',
        'currency',
        'status', // pending, paid, failed, refunded
        'payment_method',
        'paid_at',
        'due_date',
        'invoice_number',
        'metadata', // JSON
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'due_date' => 'date',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
