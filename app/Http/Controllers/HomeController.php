<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Middleware\InitializeTenancyBySession;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер главной страницы после логина.
 * /dashboard — суперадмин → central, tenant user → tenant dashboard или выбор компании.
 */
class HomeController extends Controller
{
    public function __construct(
        protected DashboardController $tenantDashboard
    ) {
    }

    /**
     * Показать dashboard или страницу выбора tenant
     */
    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Суперадмин — редирект на central, кроме случая когда явно выбран tenant (set-tenant)
        $sessionTenantId = session(InitializeTenancyBySession::SESSION_KEY);
        if ($user && ($user->is_super_admin || $user->hasRole('super_admin')) && !$sessionTenantId) {
            return redirect()->route('central.dashboard');
        }

        // Если суперадмин с tenant в сессии — показать tenant dashboard
        if ($sessionTenantId && $user) {
            $tenant = Tenant::find($sessionTenantId);
            if ($tenant) {
                return $this->showTenantDashboard($tenant);
            }
        }

        $tenants = $this->getUserTenants($user);

        // Если один tenant — ставим в сессию и показываем dashboard
        if ($tenants->count() === 1) {
            return $this->showTenantDashboard($tenants->first());
        }

        // Несколько tenants — если в сессии есть валидный, показываем его dashboard
        $sessionTenantId = session(InitializeTenancyBySession::SESSION_KEY);
        if ($sessionTenantId && $tenants->contains('id', $sessionTenantId)) {
            return $this->showTenantDashboard($tenants->firstWhere('id', $sessionTenantId));
        }

        // Нет tenant в сессии или невалидный — показываем выбор компании
        return Inertia::render('Home', [
            'tenants' => $tenants->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'email' => $t->email,
            ]),
        ]);
    }

    /**
     * Инициализировать tenancy и отрендерить tenant dashboard
     */
    protected function showTenantDashboard(Tenant $tenant): Response
    {
        session()->put(InitializeTenancyBySession::SESSION_KEY, $tenant->id);
        session()->save(); // Явно сохраняем сессию
        tenancy()->initialize($tenant);

        try {
            return $this->tenantDashboard->index(request());
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Получить tenants, где у пользователя есть запись Staff
     */
    protected function getUserTenants($user)
    {
        if (!$user) {
            return collect();
        }

        $tenantIds = [];
        // Включаем active и trial — пользователь должен видеть все свои компании
        $tenants = Tenant::whereIn('status', ['active', 'trial'])->get();

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);
                $staff = \App\Models\Tenant\Staff::where('user_id', $user->id)->first();
                if ($staff) {
                    $tenantIds[] = $tenant->id;
                }
            } catch (\Exception $e) {
                continue;
            } finally {
                tenancy()->end();
            }
        }

        // Если не нашли среди active/trial — проверяем все tenants (на случай suspended)
        if (empty($tenantIds)) {
            $allTenants = Tenant::all();
            foreach ($allTenants as $tenant) {
                try {
                    tenancy()->initialize($tenant);
                    $staff = \App\Models\Tenant\Staff::where('user_id', $user->id)->first();
                    if ($staff) {
                        $tenantIds[] = $tenant->id;
                    }
                } catch (\Exception $e) {
                } finally {
                    tenancy()->end();
                }
            }
        }

        return Tenant::whereIn('id', $tenantIds)->orderBy('name')->get();
    }
}
