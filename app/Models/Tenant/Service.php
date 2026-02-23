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
 * @property int|null $location_id
 * @property bool $is_active
 * @property string $booking_mode
 * @property int $buffer_time_before
 * @property int $buffer_time_after
 * @property int $prepare_time
 * @property int|null $max_participants
 * @property int|null $min_duration
 * @property int|null $max_duration
 * @property bool $allow_custom_duration
 * @property bool $allow_recurring
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
        'location_id',
        'is_active',
        'booking_mode', // service, hotel, event, online, rental, chauffeur
        'buffer_time_before',
        'buffer_time_after',
        'prepare_time',
        'max_participants',
        'min_duration',
        'max_duration',
        'allow_custom_duration',
        'allow_recurring',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_custom_duration' => 'boolean',
        'allow_recurring' => 'boolean',
        'metadata' => 'array',
        'price' => 'decimal:2',
        'duration' => 'integer',
        'buffer_time_before' => 'integer',
        'buffer_time_after' => 'integer',
        'prepare_time' => 'integer',
        'max_participants' => 'integer',
        'min_duration' => 'integer',
        'max_duration' => 'integer',
    ];

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'service_staff');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
