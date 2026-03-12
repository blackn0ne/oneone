<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Сервис для работы с бронированиями
 */

class BookingService
{
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $service = Service::findOrFail($data['service_id']);
            
            // Проверка доступности
            $this->checkAvailability(
                $service,
                Carbon::parse($data['start_time']),
                Carbon::parse($data['end_time']),
                $data['staff_id'] ?? null
            );

            // Расчет цены
            $price = $this->calculatePrice($service, $data);

            // Создание бронирования
            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'service_id' => $data['service_id'],
                'staff_id' => $data['staff_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'business_id' => $data['business_id'] ?? null,
                'status' => $data['status'] ?? BookingStatus::PENDING->value,
                'booking_mode' => $service->booking_mode,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration' => $data['duration'] ?? $service->duration,
                'participants_count' => $data['participants_count'] ?? 1,
                'is_group' => $data['is_group'] ?? false,
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurring_pattern' => $data['recurring_pattern'] ?? null,
                'recurring_end_date' => $data['recurring_end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'price' => $price,
                'total_price' => $price,
                'currency' => $data['currency'] ?? 'KZT',
            ]);

            // Создание повторяющихся бронирований
            if ($data['is_recurring'] ?? false) {
                $this->createRecurringBookings($booking, $data);
            }

            return $booking;
        });
    }

    /**
     * Проверить доступность временного слота
     *
     * @param Service $service
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param int|null $staffId
     * @return void
     * @throws \Exception
     */
    protected function checkAvailability(
        Service $service,
        Carbon $startTime,
        Carbon $endTime,
        ?int $staffId
    ): void {
        $query = Booking::where('status', '!=', BookingStatus::CANCELLED->value)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($staffId) {
            $query->where('staff_id', $staffId);
        } else {
            $query->where('service_id', $service->id);
        }

        if ($query->exists()) {
            throw new \Exception('Time slot is not available');
        }
    }

    protected function calculatePrice(Service $service, array $data): float
    {
        $price = $service->price;

        // Множественные участники
        if (($data['is_group'] ?? false) && $service->max_participants) {
            $price *= ($data['participants_count'] ?? 1);
        }

        // Кастомная длительность
        if (($data['custom_duration'] ?? false) && $service->allow_custom_duration) {
            $duration = $data['duration'] ?? $service->duration;
            $price = ($price / $service->duration) * $duration;
        }

        return round($price, 2);
    }

    protected function createRecurringBookings(Booking $parent, array $data): void
    {
        $pattern = $data['recurring_pattern'];
        $endDate = Carbon::parse($data['recurring_end_date']);
        $current = Carbon::parse($parent->start_time);

        while ($current->lte($endDate)) {
            if ($current->eq($parent->start_time)) {
                $current->add($this->getRecurringInterval($pattern));
                continue;
            }

            Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'service_id' => $parent->service_id,
                'staff_id' => $parent->staff_id,
                'customer_id' => $parent->customer_id,
                'business_id' => $parent->business_id,
                'status' => BookingStatus::PENDING->value,
                'booking_mode' => $parent->booking_mode,
                'start_time' => $current->copy(),
                'end_time' => $current->copy()->addMinutes($parent->duration),
                'duration' => $parent->duration,
                'participants_count' => $parent->participants_count,
                'is_group' => $parent->is_group,
                'is_recurring' => true,
                'recurring_pattern' => $pattern,
                'parent_booking_id' => $parent->id,
                'price' => $parent->price,
                'total_price' => $parent->total_price,
                'currency' => $parent->currency,
            ]);

            $current->add($this->getRecurringInterval($pattern));
        }
    }

    protected function getRecurringInterval(string $pattern): \DateInterval
    {
        return match($pattern) {
            'daily' => new \DateInterval('P1D'),
            'weekly' => new \DateInterval('P1W'),
            'monthly' => new \DateInterval('P1M'),
            default => new \DateInterval('P1W'),
        };
    }

    protected function generateBookingNumber(): string
    {
        return 'BK' . date('Ymd') . strtoupper(Str::random(8));
    }
}
