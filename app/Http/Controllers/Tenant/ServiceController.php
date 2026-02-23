<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления услугами
 */
class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $services = Service::with('location')
            ->latest()
            ->paginate(15);

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    public function create(Request $request): Response
    {
        $locations = Location::where('is_active', true)->get();

        return Inertia::render('Services/Create', [
            'locations' => $locations,
        ]);
    }

    /**
     * Сохранить новую услугу
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'is_active' => ['boolean'],
            'booking_mode' => ['required', 'in:service,hotel,event,online,rental,chauffeur'],
            'buffer_time_before' => ['nullable', 'integer', 'min:0'],
            'buffer_time_after' => ['nullable', 'integer', 'min:0'],
            'prepare_time' => ['nullable', 'integer', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'allow_custom_duration' => ['boolean'],
            'allow_recurring' => ['boolean'],
        ]);

        $service = Service::create($validated);

        return redirect()
            ->route('services.show', $service)
            ->with('success', 'Услуга успешно создана!');
    }

    public function show(Service $service): Response
    {
        $service->load(['location', 'staff', 'bookings']);

        return Inertia::render('Services/Show', [
            'service' => $service,
        ]);
    }

    /**
     * Обновить услугу
     *
     * @param Request $request
     * @param Service $service
     * @return RedirectResponse
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'is_active' => ['boolean'],
            'booking_mode' => ['sometimes', 'required', 'in:service,hotel,event,online,rental,chauffeur'],
            'buffer_time_before' => ['nullable', 'integer', 'min:0'],
            'buffer_time_after' => ['nullable', 'integer', 'min:0'],
            'prepare_time' => ['nullable', 'integer', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'allow_custom_duration' => ['boolean'],
            'allow_recurring' => ['boolean'],
        ]);

        $service->update($validated);

        return redirect()
            ->route('services.show', $service)
            ->with('success', 'Услуга обновлена!');
    }

    /**
     * Удалить услугу
     *
     * @param Service $service
     * @return RedirectResponse
     */
    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Услуга удалена!');
    }
}
