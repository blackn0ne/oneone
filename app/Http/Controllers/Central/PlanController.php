<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePlanRequest;
use App\Http\Requests\Central\UpdatePlanRequest;
use App\Models\Central\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления тарифными планами
 */
class PlanController extends Controller
{
    /**
     * Отобразить список всех планов
     *
     * @return Response
     */
    public function index(): Response
    {
        $plans = Plan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->paginate(15);

        return Inertia::render('Central/Plans/Index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Показать форму создания нового плана
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Central/Plans/Create');
    }

    /**
     * Сохранить новый план
     *
     * @param StorePlanRequest $request
     * @return RedirectResponse
     */
    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return redirect()
            ->route('central.plans.index')
            ->with('success', 'Тарифный план успешно создан!');
    }

    /**
     * Отобразить детальную информацию о плане
     *
     * @param Plan $plan
     * @return Response
     */
    public function show(Plan $plan): Response
    {
        $plan->load(['subscriptions.tenant', 'tenants']);

        return Inertia::render('Central/Plans/Show', [
            'plan' => $plan,
        ]);
    }

    /**
     * Показать форму редактирования плана
     *
     * @param Plan $plan
     * @return Response
     */
    public function edit(Plan $plan): Response
    {
        return Inertia::render('Central/Plans/Edit', [
            'plan' => $plan,
        ]);
    }

    /**
     * Обновить план
     *
     * @param UpdatePlanRequest $request
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()
            ->route('central.plans.show', $plan)
            ->with('success', 'Тарифный план обновлен!');
    }

    /**
     * Удалить план
     *
     * @param Plan $plan
     * @return RedirectResponse
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        // Проверяем, есть ли активные подписки
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return back()
                ->withErrors(['error' => 'Нельзя удалить план с активными подписками.']);
        }

        $plan->delete();

        return redirect()
            ->route('central.plans.index')
            ->with('success', 'Тарифный план удален!');
    }
}
