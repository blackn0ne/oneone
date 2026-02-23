<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/**
 * Модель домена tenant
 *
 * @property int $id
 * @property string $domain
 * @property string $tenant_id
 */
class Domain extends BaseDomain
{
    protected $connection = 'central';

    /**
     * Связь с tenant
     *
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
