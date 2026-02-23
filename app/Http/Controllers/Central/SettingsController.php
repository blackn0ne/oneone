<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\UpdateEmailSettingsRequest;
use App\Http\Requests\Central\UpdateGeneralSettingsRequest;
use App\Http\Requests\Central\UpdatePaymentSettingsRequest;
use App\Http\Requests\Central\UpdateWhatsAppSettingsRequest;
use App\Models\Central\Settings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления настройками центрального приложения
 */
class SettingsController extends Controller
{
    /**
     * Отобразить страницу настроек
     *
     * @return Response
     */
    public function index(): Response
    {
        $settings = Settings::getInstance();

        return Inertia::render('Central/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Обновить общие настройки
     *
     * @param UpdateGeneralSettingsRequest $request
     * @return RedirectResponse
     */
    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $settings = Settings::getInstance();
        $settings->update($request->validated());

        return back()->with('success', 'Общие настройки успешно обновлены!');
    }

    /**
     * Обновить настройки платежей
     *
     * @param UpdatePaymentSettingsRequest $request
     * @return RedirectResponse
     */
    public function updatePayment(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $settings = Settings::getInstance();
        $settings->update($request->validated());

        return back()->with('success', 'Настройки платежей успешно обновлены!');
    }

    /**
     * Обновить настройки email
     *
     * @param UpdateEmailSettingsRequest $request
     * @return RedirectResponse
     */
    public function updateEmail(UpdateEmailSettingsRequest $request): RedirectResponse
    {
        $settings = Settings::getInstance();
        $settings->update($request->validated());

        return back()->with('success', 'Настройки email успешно обновлены!');
    }

    /**
     * Обновить настройки WhatsApp
     *
     * @param UpdateWhatsAppSettingsRequest $request
     * @return RedirectResponse
     */
    public function updateWhatsApp(UpdateWhatsAppSettingsRequest $request): RedirectResponse
    {
        $settings = Settings::getInstance();
        $settings->update($request->validated());

        return back()->with('success', 'Настройки WhatsApp успешно обновлены!');
    }
}
