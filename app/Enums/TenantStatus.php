<?php

namespace App\Enums;

enum TenantStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case TRIAL = 'trial';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Активен',
            self::SUSPENDED => 'Приостановлен',
            self::TRIAL => 'Пробный период',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'default',
            self::SUSPENDED => 'destructive',
            self::TRIAL => 'secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
