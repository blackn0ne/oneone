<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель точки продаж
 *
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property bool $is_active
 * @property array|null $metadata
 */
class Business extends Model
{
    protected $table = 'business';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'business_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'business_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'business_id');
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class, 'business_id');
    }

    /**
     * Scope: только активные бизнесы
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: только неактивные бизнесы
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
