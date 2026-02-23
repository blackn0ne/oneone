<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Http\Requests\Central\UpdateTenantRequest;
use App\Models\Central\Tenant;
use App\Models\Central\Plan;
use App\Services\Central\TenantService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления tenants (клиентами платформы)
 */
class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {
    }

    /**
     * Отобразить список всех tenants
     *
     * @return Response
     */
    public function index(): Response
    {
        $tenants = Tenant::with(['plan', 'subscription'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Показать форму создания нового tenant
     *
     * @return Response
     */
    public function create(): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Central/Tenants/Create', [
            'plans' => $plans,
        ]);
    }

    /**
     * Сохранить нового tenant
     *
     * @param StoreTenantRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = $this->tenantService->createTenant($request->validated());

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant успешно создан!');
    }

    /**
     * Отобразить детальную информацию о tenant
     *
     * @param Tenant $tenant
     * @return Response
     */
    public function show(Tenant $tenant): Response
    {
        $tenant->load(['plan', 'subscription', 'domains', 'billings']);

        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Показать форму редактирования tenant
     *
     * @param Tenant $tenant
     * @return Response
     */
    public function edit(Tenant $tenant): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Central/Tenants/Edit', [
            'tenant' => $tenant->load(['plan', 'subscription', 'domains']),
            'plans' => $plans,
        ]);
    }

    /**
     * Обновить tenant
     *
     * @param UpdateTenantRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->tenantService->updateTenant($tenant, $request->validated());

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant обновлен!');
    }

    /**
     * Удалить tenant
     *
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->tenantService->deleteTenant($tenant);

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Tenant удален!');
    }
}
