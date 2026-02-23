<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Service;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для дашборда tenant
 */
class DashboardController extends Controller
{
    /**
     * Отобразить дашборд со статистикой
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $stats = $this->getStats();
        $recentBookings = $this->getRecentBookings();
        $upcomingBookings = $this->getUpcomingBookings();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }

    /**
     * Получить статистику
     *
     * @return array<string, mixed>
     */
    protected function getStats(): array
    {
        return [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', BookingStatus::PENDING->value)->count(),
            'confirmed_bookings' => Booking::where('status', BookingStatus::CONFIRMED->value)->count(),
            'total_revenue' => Booking::where('payment_status', 'paid')->sum('total_price'),
            'total_services' => Service::where('is_active', true)->count(),
            'total_customers' => Customer::count(),
        ];
    }

    /**
     * Получить последние бронирования
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecentBookings()
    {
        return Booking::with(['service', 'customer', 'staff'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Получить предстоящие бронирования
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUpcomingBookings()
    {
        return Booking::with(['service', 'customer'])
            ->where('start_time', '>=', now())
            ->where('status', '!=', BookingStatus::CANCELLED->value)
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }
}
