<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Установка текущего tenant в сессию (для пользователей с несколькими компаниями)
 */
class SetTenantController extends Controller
{
    public function __invoke(Request $request, string $tenantId): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $tenantModel = Tenant::find($tenantId);
        if (!$tenantModel) {
            return redirect()->route('dashboard')->with('error', 'Компания не найдена.');
        }

        // Суперадмин может выбрать любую компанию
        if ($user->is_super_admin || $user->hasRole('super_admin')) {
            // OK
        } else {
            // Проверяем, что у пользователя есть доступ (staff)
            try {
                tenancy()->initialize($tenantModel);
                $hasAccess = \App\Models\Tenant\Staff::where('user_id', $user->id)->exists();
            } catch (\Throwable $e) {
                return redirect()->route('dashboard')->with('error', 'Нет доступа к этой компании.');
            } finally {
                tenancy()->end();
            }

            if (!$hasAccess) {
                return redirect()->route('dashboard')->with('error', 'Нет доступа к этой компании.');
            }
        }

        session()->put(\App\Http\Middleware\InitializeTenancyBySession::SESSION_KEY, $tenantId);
        session()->save(); // Явно сохраняем сессию

        $redirect = $request->query('redirect', route('dashboard'));

        return redirect($redirect);
    }
}
