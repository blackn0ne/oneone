<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Service;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Показать страницу отчетов
     */
    public function index(Request $request): Response
    {
        // Получаем фильтры из запроса
        $dateFrom = $request->input('date_from') ?: null;
        $dateTo = $request->input('date_to') ?: null;
        $status = $request->input('status') ?: null;
        $serviceId = $request->input('service_id') ? (int) $request->input('service_id') : null;
        $staffId = $request->input('staff_id') ? (int) $request->input('staff_id') : null;

        // Базовый запрос для бронирований с фильтрами
        $bookingsQuery = Booking::query();

        if ($dateFrom) {
            $bookingsQuery->whereDate('start_time', '>=', $dateFrom);
        }

        if ($dateTo) {
            $bookingsQuery->whereDate('start_time', '<=', $dateTo);
        }

        if ($status) {
            $bookingsQuery->where('status', $status);
        }

        if ($serviceId) {
            $bookingsQuery->where('service_id', $serviceId);
        }

        if ($staffId) {
            $bookingsQuery->where('staff_id', $staffId);
        }

        // Статистика по бронированиям
        $bookingsStats = [
            'total' => Booking::count(),
            'today' => Booking::whereDate('start_time', today())->count(),
            'this_week' => Booking::whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Booking::whereMonth('start_time', now()->month)
                ->whereYear('start_time', now()->year)
                ->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'filtered' => (clone $bookingsQuery)->count(),
        ];

        // Статистика по услугам
        $servicesStats = [
            'total' => Service::count(),
            'active' => Service::where('is_active', true)->count(),
        ];

        // Статистика по сотрудникам
        $staffStats = [
            'total' => Staff::count(),
            'active' => Staff::where('is_active', true)->count(),
        ];

        // Статистика по клиентам
        $customersStats = [
            'total' => Customer::count(),
        ];

        // Топ услуги по количеству бронирований (с фильтрами)
        $topServicesQuery = Service::withCount(['bookings' => function ($query) use ($dateFrom, $dateTo, $status, $staffId) {
            if ($dateFrom) {
                $query->whereDate('start_time', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('start_time', '<=', $dateTo);
            }
            if ($status) {
                $query->where('status', $status);
            }
            if ($staffId) {
                $query->where('staff_id', $staffId);
            }
        }]);

        if ($serviceId) {
            $topServicesQuery->where('id', $serviceId);
        }

        $topServices = $topServicesQuery
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'bookings_count' => $service->bookings_count,
                ];
            });

        // Топ сотрудники по количеству бронирований (с фильтрами)
        $topStaffQuery = Staff::withCount(['bookings' => function ($query) use ($dateFrom, $dateTo, $status, $serviceId) {
            if ($dateFrom) {
                $query->whereDate('start_time', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('start_time', '<=', $dateTo);
            }
            if ($status) {
                $query->where('status', $status);
            }
            if ($serviceId) {
                $query->where('service_id', $serviceId);
            }
        }]);

        if ($staffId) {
            $topStaffQuery->where('id', $staffId);
        }

        $topStaff = $topStaffQuery
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'bookings_count' => $staff->bookings_count,
                ];
            });

        // Бронирования по месяцам (с фильтрами)
        $monthlyBookingsQuery = Booking::select(
                DB::raw('DATE_FORMAT(start_time, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            );

        if ($dateFrom) {
            $monthlyBookingsQuery->whereDate('start_time', '>=', $dateFrom);
        } else {
            $monthlyBookingsQuery->where('start_time', '>=', now()->subMonths(6));
        }

        if ($dateTo) {
            $monthlyBookingsQuery->whereDate('start_time', '<=', $dateTo);
        }

        if ($status) {
            $monthlyBookingsQuery->where('status', $status);
        }

        if ($serviceId) {
            $monthlyBookingsQuery->where('service_id', $serviceId);
        }

        if ($staffId) {
            $monthlyBookingsQuery->where('staff_id', $staffId);
        }

        $monthlyBookings = $monthlyBookingsQuery
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'count' => $item->count,
                ];
            });

        // Получаем списки для фильтров
        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $staffList = Staff::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reports/Index', [
            'bookingsStats' => $bookingsStats,
            'servicesStats' => $servicesStats,
            'staffStats' => $staffStats,
            'customersStats' => $customersStats,
            'topServices' => $topServices,
            'topStaff' => $topStaff,
            'monthlyBookings' => $monthlyBookings,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
                'service_id' => $serviceId,
                'staff_id' => $staffId,
            ],
            'services' => $services,
            'staffList' => $staffList,
        ]);
    }
}
