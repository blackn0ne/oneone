<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use App\Http\Controllers\Tenant\LandingController;

/*
|--------------------------------------------------------------------------
| Tenant Routes (path-based)
|--------------------------------------------------------------------------
|
| /{tenant} — публичный сайт компании (лого, услуги, запись)
| Панель tenant: /dashboard, /bookings и т.д. — в web.php (tenant из сессии)
|
*/

Route::middleware(['web', InitializeTenancyByPath::class])->group(function () {
    Route::get('/{tenant}', [LandingController::class, 'index'])->name('tenant.landing');
});
