<?php

namespace App\Models\Central;

use App\Enums\TenantStatus;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'status',
            'plan_id',
            'trial_ends_at',
        ];
    }

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'status',
        'plan_id',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'data' => 'array',
    ];

    // Relationships
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    /**
     * Проверить, активен ли tenant
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE->value;
    }

    /**
     * Проверить, находится ли tenant на пробном периоде
     *
     * @return bool
     */
    public function isTrial(): bool
    {
        return $this->status === TenantStatus::TRIAL->value &&
               ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    /**
     * Проверить, приостановлен ли tenant
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED->value;
    }
}
