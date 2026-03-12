<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Business;
use App\Models\Tenant\WorkingHour;
use Illuminate\Support\Collection;

class BusinessService
{
    /**
     * Дни недели по умолчанию
     */
    private const DEFAULT_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * Значения по умолчанию для рабочих часов
     */
    private const DEFAULT_WORKING_HOURS = [
        'is_closed' => false,
        'start' => '08:00',
        'end' => '22:00',
    ];

    /**
     * Форматировать время из формата HH:MM:SS в HH:MM
     *
     * @param string|null $time
     * @param string $default
     * @return string
     */
    private function formatTime(?string $time, string $default = '08:00'): string
    {
        if (!$time) {
            return $default;
        }

        // Если время в формате HH:MM:SS, обрезаем до HH:MM
        if (strlen($time) > 5) {
            return substr($time, 0, 5);
        }

        return $time;
    }

    /**
     * Форматировать рабочие часы в структуру для фронтенда
     *
     * @param Collection $workingHours
     * @return array
     */
    public function formatWorkingHours(Collection $workingHours): array
    {
        $formatted = [];

        foreach ($workingHours as $wh) {
            // Получаем сырое значение из базы данных (может быть в формате HH:MM:SS)
            $attributes = $wh->getAttributes();
            $rawStart = $attributes['start'] ?? null;
            $rawEnd = $attributes['end'] ?? null;

            $formatted[$wh->day_of_week] = [
                'is_closed' => $wh->is_closed,
                'start' => $this->formatTime($rawStart, self::DEFAULT_WORKING_HOURS['start']),
                'end' => $this->formatTime($rawEnd, self::DEFAULT_WORKING_HOURS['end']),
            ];
        }

        // Заполняем отсутствующие дни значениями по умолчанию
        foreach (self::DEFAULT_DAYS as $day) {
            if (!isset($formatted[$day])) {
                $formatted[$day] = self::DEFAULT_WORKING_HOURS;
            }
        }

        return $formatted;
    }

    /**
     * Синхронизировать рабочие часы для бизнеса
     *
     * @param Business $business
     * @param array $workingHours
     * @return void
     */
    public function syncWorkingHours(Business $business, array $workingHours): void
    {
        foreach (self::DEFAULT_DAYS as $day) {
            $dayData = $workingHours[$day] ?? self::DEFAULT_WORKING_HOURS;

            WorkingHour::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'day_of_week' => $day,
                ],
                [
                    'is_closed' => $dayData['is_closed'] ?? self::DEFAULT_WORKING_HOURS['is_closed'],
                    'start' => $dayData['start'] ?? self::DEFAULT_WORKING_HOURS['start'],
                    'end' => $dayData['end'] ?? self::DEFAULT_WORKING_HOURS['end'],
                ]
            );
        }
    }

    /**
     * Получить данные бизнеса для формы редактирования
     *
     * @param Business $business
     * @return array
     */
    public function getBusinessForEdit(Business $business): array
    {
        $business->load('workingHours');

        return [
            'id' => $business->id,
            'name' => $business->name,
            'address' => $business->address,
            'phone' => $business->phone,
            'email' => $business->email,
            'is_active' => (bool) $business->is_active,
            'working_hours' => $this->formatWorkingHours($business->workingHours),
            'created_at' => $business->created_at,
            'updated_at' => $business->updated_at,
        ];
    }
}
