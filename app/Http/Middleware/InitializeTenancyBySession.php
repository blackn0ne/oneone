<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

/**
 * Инициализация tenancy по tenant_id из сессии.
 * Используется для маршрутов /dashboard, /bookings и т.д. без tenant в URL.
 */
class InitializeTenancyBySession
{
    public const SESSION_KEY = 'current_tenant_id';

    public function __construct(
        protected Tenancy $tenancy
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Читаем сессию из центральной БД (до инициализации tenancy)
        // Убеждаемся, что используем центральное подключение
        $tenantId = session(self::SESSION_KEY);

        if (!$tenantId) {
            return redirect()->route('dashboard');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget(self::SESSION_KEY);
            return redirect()->route('dashboard');
        }

        try {
            $this->tenancy->initialize($tenant);
        } catch (\Throwable $e) {
            session()->forget(self::SESSION_KEY);
            return redirect()->route('dashboard')->with('error', 'Ошибка доступа к базе данных компании.');
        }

        try {
            $response = $next($request);
        } finally {
            $this->tenancy->end();
        }

        return $response;
    }
}
