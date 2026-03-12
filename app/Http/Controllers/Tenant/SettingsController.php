<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Показать настройки компании
     */
    public function index(Request $request): Response
    {
        $settings = Settings::getInstance();

        // Преобразуем в массив для передачи во фронтенд
        $settingsData = $settings->toArray();

        return Inertia::render('Settings/Index', [
            'settings' => $settingsData,
        ]);
    }

    /**
     * Обновить настройки компании
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
            'social_links' => ['nullable', 'array'],
            'global_currency' => ['nullable', 'string', 'size:3'],
            'default_language' => ['nullable', 'string', 'max:10'],
            'languages' => ['nullable', 'array'],
        ]);

        $settings = Settings::getInstance();
        $settings->update($validated);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Настройки компании обновлены!');
    }
}
