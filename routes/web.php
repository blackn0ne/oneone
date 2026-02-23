<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\SubscriptionController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\SettingsController;
use App\Http\Controllers\Central\LanguageController;

/*
|--------------------------------------------------------------------------
| Central Application Routes
|--------------------------------------------------------------------------
|
| These routes are for the central application (SaaS platform management).
| Tenant routes are in routes/tenant.php
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Central routes (tenant management) - только для суперадминов
Route::middleware(['auth', 'verified', 'super_admin'])->prefix('central')->name('central.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class);
    Route::resource('plans', PlanController::class);
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('languages', LanguageController::class);
    Route::post('/languages/{language}/translations', [LanguageController::class, 'updateTranslations'])->name('languages.translations.update');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
    Route::post('/settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment.update');
    Route::post('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
    Route::post('/settings/whatsapp', [SettingsController::class, 'updateWhatsApp'])->name('settings.whatsapp.update');
});

// Dashboard - редирект в зависимости от роли
Route::get('dashboard', function () {
    $user = auth()->user();
    
    // Если суперадмин - редирект на центральный dashboard
    if ($user && ($user->is_super_admin || $user->hasRole('super_admin'))) {
        return redirect()->route('central.dashboard');
    }
    
    // Иначе - обычный dashboard (для tenant)
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
