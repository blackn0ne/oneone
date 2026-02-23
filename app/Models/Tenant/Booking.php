<?php

namespace App\Models\Tenant;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель бронирования
 *
 * @property int $id
 * @property string $booking_number
 * @property int $service_id
 * @property int|null $staff_id
 * @property int $customer_id
 * @property int|null $location_id
 * @property BookingStatus|string $status
 * @property string $booking_mode
 * @property \Carbon\Carbon $start_time
 * @property \Carbon\Carbon $end_time
 * @property int $duration
 * @property int $participants_count
 * @property bool $is_group
 * @property bool $is_recurring
 * @property string|null $recurring_pattern
 * @property \Carbon\Carbon|null $recurring_end_date
 * @property int|null $parent_booking_id
 * @property float $price
 * @property float $deposit
 * @property float $total_price
 * @property string $currency
 * @property string $payment_status
 * @property string|null $payment_method
 * @property string|null $notes
 * @property array|null $metadata
 */
class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_number',
        'service_id',
        'staff_id',
        'customer_id',
        'location_id',
        'status',
        'booking_mode',
        'start_time',
        'end_time',
        'duration',
        'participants_count',
        'is_group',
        'is_recurring',
        'recurring_pattern',
        'recurring_end_date',
        'parent_booking_id',
        'price',
        'deposit',
        'total_price',
        'currency',
        'payment_status',
        'payment_method',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurring_end_date' => 'date',
        'is_group' => 'boolean',
        'is_recurring' => 'boolean',
        'price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'total_price' => 'decimal:2',
        'metadata' => 'array',
        'duration' => 'integer',
        'participants_count' => 'integer',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function parentBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    public function childBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'parent_booking_id');
    }
}
