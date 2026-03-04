<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Business;
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
        $business = Business::getInstance();
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'duration', 'price', 'booking_mode']);

        return Inertia::render('Tenant/Landing', [
            'tenantId' => $tenant,
            'business' => $business ? [
                'company_name' => $business->company_name,
                'company_slogan' => $business->company_slogan,
                'logo' => $business->logo,
                'phone' => $business->phone,
                'email' => $business->email,
                'address' => $business->address ? trim(implode(', ', array_filter([
                    $business->country,
                    $business->city,
                    $business->address,
                ]))) : null,
            ] : null,
            'services' => $services,
        ]);
    }
}
