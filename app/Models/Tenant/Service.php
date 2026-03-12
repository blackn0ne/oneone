<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель услуги
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $duration
 * @property float $price
 * @property int|null $category_id
 * @property int|null $business_id
 * @property bool $is_active
 * @property string $booking_mode
 * @property array|null $metadata
 */
class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration', // в минутах
        'price',
        'category_id',
        'business_id',
        'is_active',
        'booking_mode', // service, hotel, event, online, rental, chauffeur
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'service_staff');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
