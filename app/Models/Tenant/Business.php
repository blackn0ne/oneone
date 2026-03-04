<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель бизнес-настроек tenant
 */
class Business extends Model
{
    protected $connection = 'tenant';

    protected $table = 'business';

    protected $fillable = [
        'company_name',
        'company_slogan',
        'logo',
        'favicon',
        'phone',
        'email',
        'country',
        'city',
        'address',
        'working_hours',
        'social_links',
        'global_currency',
        'default_language',
        'languages',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'social_links' => 'array',
        'languages' => 'array',
    ];

    /**
     * Получить единственную запись настроек (singleton)
     */
    public static function getInstance(): ?self
    {
        return static::first();
    }
}
