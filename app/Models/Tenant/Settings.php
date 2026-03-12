<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель настроек компании tenant
 *
 * @property int $id
 * @property string|null $company_name
 * @property string|null $company_slogan
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $logo
 * @property string|null $favicon
 * @property string $global_currency
 * @property string $default_language
 * @property array|null $languages
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $country
 * @property string|null $city
 * @property string|null $address
 * @property array|null $social_links
 * @property bool $bank_transfer_enabled
 * @property string|null $bank_name
 * @property string|null $bank_account
 * @property string|null $bank_swift
 * @property string|null $bank_iban
 * @property string|null $bank_instructions
 * @property string|null $smtp_host
 * @property int|null $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property string|null $smtp_encryption
 * @property string|null $smtp_from_address
 * @property string|null $smtp_from_name
 * @property bool $whatsapp_enabled
 * @property string|null $whatsapp_api_key
 * @property string|null $whatsapp_api_secret
 * @property string|null $whatsapp_phone_number
 * @property string|null $whatsapp_business_id
 * @property string|null $whatsapp_webhook_url
 */
class Settings extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'company_name',
        'company_slogan',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'logo',
        'favicon',
        'global_currency',
        'default_language',
        'languages',
        'phone',
        'email',
        'country',
        'city',
        'address',
        'social_links',
        'bank_transfer_enabled',
        'bank_name',
        'bank_account',
        'bank_swift',
        'bank_iban',
        'bank_instructions',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
        'whatsapp_enabled',
        'whatsapp_api_key',
        'whatsapp_api_secret',
        'whatsapp_phone_number',
        'whatsapp_business_id',
        'whatsapp_webhook_url',
    ];

    protected $casts = [
        'languages' => 'array',
        'social_links' => 'array',
        'bank_transfer_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'smtp_port' => 'integer',
    ];

    /**
     * Получить единственную запись настроек или создать новую
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate([]);
    }
}
