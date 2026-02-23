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
        $bookings = Booking::with(['service', 'customer', 'staff', 'location'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
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
            'customer_id' => ['required', 'exists:customers,id'],
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
            $booking = $this->bookingService->createBooking($validated);

            return redirect()
                ->route('bookings.show', $booking)
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
