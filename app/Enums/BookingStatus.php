<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Ожидает',
            self::CONFIRMED => 'Подтверждено',
            self::CANCELLED => 'Отменено',
            self::COMPLETED => 'Завершено',
            self::NO_SHOW => 'Не явился',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'secondary',
            self::CONFIRMED => 'default',
            self::CANCELLED => 'destructive',
            self::COMPLETED => 'default',
            self::NO_SHOW => 'destructive',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
