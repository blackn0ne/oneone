<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreBusinessRequest;
use App\Http\Requests\Tenant\UpdateBusinessRequest;
use App\Models\Tenant\Business;
use App\Services\Tenant\BusinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function __construct(
        private readonly BusinessService $businessService
    ) {
    }

    /**
     * Показать список точек продаж
     */
    public function index(Request $request): Response
    {
        $businesses = Business::latest()
            ->paginate(15);

        return Inertia::render('Business/Index', [
            'businesses' => $businesses,
        ]);
    }

    /**
     * Показать форму создания точки продаж
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Business/Create');
    }

    /**
     * Сохранить новую точку продаж
     */
    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $business = Business::create($validated);

        // Синхронизируем рабочие часы
        if (isset($validated['working_hours'])) {
            $this->businessService->syncWorkingHours($business, $validated['working_hours']);
        }

        return redirect()
            ->route('business.index')
            ->with('success', 'Точка продаж создана!');
    }

    /**
     * Показать детальную информацию о точке продаж
     */
    public function show($id): Response
    {
        $business = Business::findOrFail($id);

        return Inertia::render('Business/Show', [
            'business' => $business,
        ]);
    }

    /**
     * Показать форму редактирования точки продаж
     */
    public function edit(Request $request, $id): Response
    {
        $business = Business::findOrFail($id);

        return Inertia::render('Business/Edit', [
            'business' => $this->businessService->getBusinessForEdit($business),
        ]);
    }

    /**
     * Обновить точку продаж
     */
    public function update(UpdateBusinessRequest $request, $id): RedirectResponse
    {
        $business = Business::findOrFail($id);
        $validated = $request->validated();

        $business->update($validated);

        // Синхронизируем рабочие часы
        if (isset($validated['working_hours'])) {
            $this->businessService->syncWorkingHours($business, $validated['working_hours']);
        }

        return redirect()
            ->route('business.index')
            ->with('success', 'Точка продаж обновлена!');
    }

    /**
     * Удалить точку продаж
     */
    public function destroy($id): RedirectResponse
    {
        $business = Business::findOrFail($id);
        $business->delete();

        return redirect()
            ->route('business.index')
            ->with('success', 'Точка продаж удалена!');
    }
}
