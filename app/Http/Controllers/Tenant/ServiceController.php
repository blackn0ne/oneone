<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Business;
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
        $services = Service::with('business')
            ->latest()
            ->paginate(15);

        $businesses = Business::where('is_active', true)->get();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'businesses' => $businesses,
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
            'business_id' => ['nullable', 'exists:business,id'],
            'is_active' => ['boolean'],
            'booking_mode' => ['required', 'in:service,hotel,event,online,rental,chauffeur'],
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
        $service = Service::with(['business', 'staff', 'bookings'])->findOrFail($id);

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
        $businesses = Business::where('is_active', true)->get();

        return Inertia::render('Services/Edit', [
            'service' => $service,
            'businesses' => $businesses,
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
            'business_id' => ['nullable', 'exists:business,id'],
            'is_active' => ['boolean'],
            'booking_mode' => ['sometimes', 'required', 'in:service,hotel,event,online,rental,chauffeur'],
        ]);

        // Обработка business_id: если пустая строка, устанавливаем null
        if (isset($validated['business_id']) && $validated['business_id'] === '') {
            $validated['business_id'] = null;
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
