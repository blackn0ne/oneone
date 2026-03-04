<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    /**
     * Показать настройки бизнеса
     */
    public function index(Request $request): Response
    {
        $business = Business::getInstance();

        // Преобразуем в массив для передачи во фронтенд
        $businessData = null;
        if ($business) {
            // Получаем working_hours напрямую из модели (cast должен декодировать)
            $workingHours = $business->working_hours;
            
            // Если это строка, декодируем
            if (is_string($workingHours)) {
                $workingHours = json_decode($workingHours, true);
            }
            
            // Если null или пусто, устанавливаем пустой массив
            if (empty($workingHours) || !is_array($workingHours)) {
                $workingHours = [];
            }
            
            $businessData = $business->toArray();
            $businessData['working_hours'] = $workingHours;
        }

        return Inertia::render('Business/Index', [
            'business' => $businessData,
        ]);
    }

    /**
     * Обновить настройки бизнеса
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_slogan' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'logo' => ['nullable', 'string'],
            'favicon' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'working_hours' => ['nullable', 'array'],
            'working_hours.*.is_closed' => ['nullable', 'boolean'],
            'working_hours.*.start' => ['nullable', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'working_hours.*.end' => ['nullable', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'social_links' => ['nullable', 'array'],
            'global_currency' => ['nullable', 'string', 'size:3'],
            'default_language' => ['nullable', 'string', 'max:10'],
            'languages' => ['nullable', 'array'],
        ]);

        $business = Business::first();
        
        if (!$business) {
            $business = Business::create($validated);
        } else {
            $business->update($validated);
        }

        return redirect()
            ->route('business.index')
            ->with('success', 'Настройки бизнеса обновлены!');
    }
}
