<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case PAST_DUE = 'past_due';
    case TRIALING = 'trialing';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Активна',
            self::CANCELLED => 'Отменена',
            self::PAST_DUE => 'Просрочена',
            self::TRIALING => 'Пробный период',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'default',
            self::CANCELLED => 'destructive',
            self::PAST_DUE => 'destructive',
            self::TRIALING => 'secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
