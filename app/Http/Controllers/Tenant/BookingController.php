<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Service;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Customer;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления бронированиями
 */
class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
    }

    /**
     * Отобразить список всех бронирований
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $view = $request->input('view', 'calendar'); // calendar или list
        $startDate = null;
        $endDate = null;
        
        if ($view === 'calendar') {
            // Для календарного вида загружаем все бронирования за период
            $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
            $endDate = $request->input('end_date', now()->endOfWeek()->toDateString());
            
            $bookings = Booking::with(['service', 'customer', 'staff', 'location'])
                ->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('start_time')
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                        'service' => $booking->service ? ['id' => $booking->service->id, 'name' => $booking->service->name] : null,
                        'customer' => $booking->customer ? ['id' => $booking->customer->id, 'name' => $booking->customer->name] : null,
                        'staff' => $booking->staff ? ['id' => $booking->staff->id, 'name' => $booking->staff->name] : null,
                        'location' => $booking->location ? ['id' => $booking->location->id, 'name' => $booking->location->name] : null,
                        'status' => $booking->status,
                        'start_time' => $booking->start_time->toIso8601String(),
                        'end_time' => $booking->end_time->toIso8601String(),
                        'duration' => $booking->duration,
                        'total_price' => $booking->total_price,
                        'notes' => $booking->notes,
                    ];
                });
        } else {
            // Для списка используем пагинацию
            $bookings = Booking::with(['service', 'customer', 'staff', 'location'])
                ->latest()
                ->paginate(15);
        }

        // Данные для формы создания (нужны всегда)
        $services = Service::where('is_active', true)->get();
        $staff = Staff::where('is_active', true)->get();

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'view' => $view,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'services' => $services,
            'staff' => $staff,
        ]);
    }

    /**
     * Показать форму создания нового бронирования
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $services = Service::where('is_active', true)->get();
        $staff = Staff::where('is_active', true)->get();
        $customers = Customer::latest()->limit(50)->get();

        return Inertia::render('Bookings/Create', [
            'services' => $services,
            'staff' => $staff,
            'customers' => $customers,
        ]);
    }

    /**
     * Сохранить новое бронирование
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'participants_count' => ['nullable', 'integer', 'min:1'],
            'is_group' => ['nullable', 'boolean'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurring_pattern' => ['nullable', 'in:daily,weekly,monthly'],
            'recurring_end_date' => ['nullable', 'date', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            // Найти или создать клиента по телефону
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                ]
            );

            // Обновить имя, если клиент уже существовал, но имя могло измениться
            if ($customer->name !== $validated['customer_name']) {
                $customer->update(['name' => $validated['customer_name']]);
            }

            // Добавить customer_id в данные для создания бронирования
            $validated['customer_id'] = $customer->id;
            unset($validated['customer_name'], $validated['customer_phone']);

            $booking = $this->bookingService->createBooking($validated);

            return redirect()
                ->route('bookings.index')
                ->with('success', 'Бронирование успешно создано!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Отобразить детальную информацию о бронировании
     *
     * @param Booking $booking
     * @return Response
     */
    public function show(Booking $booking): Response
    {
        $booking->load(['service', 'customer', 'staff', 'location']);

        return Inertia::render('Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Обновить бронирование
     *
     * @param Request $request
     * @param Booking $booking
     * @return RedirectResponse
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:' . implode(',', BookingStatus::values())],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking->update($validated);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Бронирование обновлено!');
    }

    /**
     * Удалить бронирование
     *
     * @param Booking $booking
     * @return RedirectResponse
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Бронирование удалено!');
    }
}
