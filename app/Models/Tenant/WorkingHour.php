<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель рабочих часов
 *
 * @property int $id
 * @property int $business_id
 * @property string $day_of_week
 * @property bool $is_closed
 * @property string $start
 * @property string $end
 */
class WorkingHour extends Model
{
    protected $table = 'working_hours';

    protected $fillable = [
        'business_id',
        'day_of_week',
        'is_closed',
        'start',
        'end',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    /**
     * Accessor для форматирования времени start в формат HH:MM
     */
    public function getStartAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }
        // Если время в формате HH:MM:SS, обрезаем до HH:MM
        if (strlen($value) > 5) {
            return substr($value, 0, 5);
        }
        return $value;
    }

    /**
     * Accessor для форматирования времени end в формат HH:MM
     */
    public function getEndAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }
        // Если время в формате HH:MM:SS, обрезаем до HH:MM
        if (strlen($value) > 5) {
            return substr($value, 0, 5);
        }
        return $value;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
