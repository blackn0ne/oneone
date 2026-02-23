<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Enums\TenantStatus;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для центрального дашборда
 */
class DashboardController extends Controller
{
    /**
     * Отобразить центральный дашборд со статистикой
     *
     * @return Response
     */
    public function index(): Response
    {
        $stats = $this->getStats();
        $recentTenants = $this->getRecentTenants();
        $recentSubscriptions = $this->getRecentSubscriptions();

        return Inertia::render('Central/Dashboard', [
            'stats' => $stats,
            'recentTenants' => $recentTenants,
            'recentSubscriptions' => $recentSubscriptions,
        ]);
    }

    /**
     * Получить статистику платформы
     *
     * @return array<string, int>
     */
    protected function getStats(): array
    {
        return [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', TenantStatus::ACTIVE->value)->count(),
            'trial_tenants' => Tenant::where('status', TenantStatus::TRIAL->value)->count(),
            'suspended_tenants' => Tenant::where('status', TenantStatus::SUSPENDED->value)->count(),
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('is_active', true)->count(),
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
        ];
    }

    /**
     * Получить последние tenants
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecentTenants()
    {
        return Tenant::with('plan')
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Получить последние подписки
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecentSubscriptions()
    {
        return Subscription::with(['tenant', 'plan'])
            ->latest()
            ->take(5)
            ->get();
    }
}
