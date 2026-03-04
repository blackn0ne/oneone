<?php

namespace App\Models\Tenant;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

/**
 * Модель сотрудника
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $photo
 * @property string|null $specialization
 * @property bool $is_active
 * @property array|null $locations
 * @property array|null $working_hours
 * @property array|null $breaks
 * @property array|null $holidays
 */
class Staff extends Model
{
    use HasRoles;

    /**
     * Используем tenant-подключение, чтобы все записи создавались в базе tenant,
     * а не в центральной базе данных.
     */
    protected $connection = 'tenant';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'photo',
        'specialization',
        'is_active',
        'locations',
        'working_hours',
        'breaks',
        'holidays',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'locations' => 'array',
        'working_hours' => 'array',
        'breaks' => 'array',
        'holidays' => 'array',
    ];

    /**
     * Связь с пользователем системы (User)
     * Staff может быть связан с User для входа в систему
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с услугами (many-to-many)
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff');
    }

    /**
     * Связь с бронированиями
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
