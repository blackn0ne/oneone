<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Settings;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер публичной главной страницы tenant (сайт компании)
 */
class LandingController extends Controller
{
    /**
     * Показать главную страницу tenant — логотип, услуги, кнопка записи
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $tenant = $request->route('tenant');
        $settings = Settings::getInstance();
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'duration', 'price', 'booking_mode']);

        return Inertia::render('Tenant/Landing', [
            'tenantId' => $tenant,
            'business' => $settings ? [
                'company_name' => $settings->company_name,
                'company_slogan' => $settings->company_slogan,
                'logo' => $settings->logo,
                'phone' => $settings->phone,
                'email' => $settings->email,
                'address' => $settings->address ? trim(implode(', ', array_filter([
                    $settings->country,
                    $settings->city,
                    $settings->address,
                ]))) : null,
            ] : null,
            'services' => $services,
        ]);
    }
}
