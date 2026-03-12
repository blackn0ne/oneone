<?php

namespace App\Jobs;

use App\Models\Tenant\Business;
use App\Services\Tenant\BusinessService;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class CreateDefaultBusinessForTenant
{
    /** @var TenantWithDatabase */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Инициализируем tenancy для работы с базой данных тенанта
        tenancy()->initialize($this->tenant);

        try {
            // Создаем роли и разрешения для tenant
            $seeder = new \Database\Seeders\TenantRolePermissionSeeder();
            $seeder->run();

            // Проверяем, не существует ли уже запись business
            if (Business::count() > 0) {
                return;
            }

            // Создаем запись business с названием тенанта
            $business = Business::create([
                'name' => $this->tenant->name,
                'is_active' => true,
            ]);

            // Создаем рабочие часы по умолчанию через сервис
            $businessService = app(BusinessService::class);
            $defaultWorkingHours = [];
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                $defaultWorkingHours[$day] = [
                    'is_closed' => false,
                    'start' => '08:00',
                    'end' => '22:00',
                ];
            }
            $businessService->syncWorkingHours($business, $defaultWorkingHours);
        } finally {
            // Завершаем tenancy
            tenancy()->end();
        }
    }
}
