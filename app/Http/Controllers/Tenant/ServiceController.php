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

        $locations = Location::where('is_active', true)->get();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'locations' => $locations,
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
            ->route('services.index')
            ->with('success', 'Услуга успешно создана!');
    }

    public function show(Request $request, $id): Response
    {
        // Используем явный поиск по ID, так как route model binding может не работать
        // из-за того, что модель находится в tenant БД
        $service = Service::with(['location', 'staff', 'bookings'])->findOrFail($id);

        return Inertia::render('Services/Show', [
            'service' => $service,
        ]);
    }

    /**
     * Показать форму редактирования услуги
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id): Response
    {
        $service = Service::findOrFail($id);
        $locations = Location::where('is_active', true)->get();

        return Inertia::render('Services/Edit', [
            'service' => $service,
            'locations' => $locations,
        ]);
    }

    /**
     * Обновить услугу
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $service = Service::findOrFail($id);

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

        // Обработка location_id: если пустая строка, устанавливаем null
        if (isset($validated['location_id']) && $validated['location_id'] === '') {
            $validated['location_id'] = null;
        }

        $service->update($validated);

        return redirect()
            ->route('services.index')
            ->with('success', 'Услуга обновлена!');
    }

    /**
     * Удалить услугу
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Услуга удалена!');
    }
}
