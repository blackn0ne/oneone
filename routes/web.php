<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SetTenantController;
use App\Http\Controllers\Central\SubscriptionController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\SettingsController;
use App\Http\Controllers\Central\LanguageController;
use App\Http\Controllers\Central\UserController;
use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\ServiceController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\CustomerController;

/*
|--------------------------------------------------------------------------
| Central Application Routes
|--------------------------------------------------------------------------
|
| /dashboard — роутер: суперадмин → central, tenant user → tenant dashboard
| /bookings, /services и т.д. — панель tenant (tenant из сессии)
| Tenant routes: routes/tenant.php — публичный лендинг /{tenant}
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
    Route::post('/tenants/{tenant}/attach-user', [TenantController::class, 'attachUser'])->name('tenants.attachUser');
    Route::post('/tenants/{tenant}/create-database', [TenantController::class, 'createDatabase'])->name('tenants.createDatabase');
    Route::post('/tenants/{tenant}/update-database', [TenantController::class, 'updateDatabase'])->name('tenants.updateDatabase');
    Route::resource('tenants', TenantController::class);
    Route::resource('users', UserController::class);
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

// Dashboard — роутер по роли
Route::get('dashboard', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Выбор компании (для пользователей с несколькими)
Route::get('set-tenant/{tenant}', SetTenantController::class)->middleware(['auth', 'verified'])->name('setTenant');

// Панель tenant: /bookings, /services и т.д. (tenant из сессии)
Route::middleware(['auth', 'verified', 'tenant.session'])->group(function () {
    Route::resource('bookings', BookingController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('customers', CustomerController::class);
    Route::get('reports', [\App\Http\Controllers\Tenant\ReportController::class, 'index'])->name('reports.index');
    Route::get('business', [\App\Http\Controllers\Tenant\BusinessController::class, 'index'])->name('business.index');
    Route::put('business', [\App\Http\Controllers\Tenant\BusinessController::class, 'update'])->name('business.update');
});

require __DIR__.'/settings.php';
