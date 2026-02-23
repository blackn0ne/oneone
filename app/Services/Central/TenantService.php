<?php

namespace App\Services\Central;

use App\Models\Central\Tenant;
use App\Models\Central\Domain;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Создать нового tenant с доменом
     *
     * @param array $data
     * @return Tenant
     */
    public function createTenant(array $data): Tenant
    {
        $tenant = Tenant::create($data);

        // Создание домена по умолчанию
        $this->createDefaultDomain($tenant, $data['id']);

        return $tenant;
    }

    /**
     * Создать домен по умолчанию для tenant
     *
     * @param Tenant $tenant
     * @param string $tenantId
     * @return Domain
     */
    public function createDefaultDomain(Tenant $tenant, string $tenantId): Domain
    {
        $domain = $tenantId . '.' . config('app.domain', 'localhost');

        return $tenant->domains()->create([
            'domain' => $domain,
        ]);
    }

    /**
     * Обновить tenant
     *
     * @param Tenant $tenant
     * @param array $data
     * @return Tenant
     */
    public function updateTenant(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->fresh();
    }

    /**
     * Удалить tenant и все связанные данные
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function deleteTenant(Tenant $tenant): bool
    {
        // Удаление всех доменов
        $tenant->domains()->delete();

        // Удаление tenant (база данных будет удалена автоматически через события)
        return $tenant->delete();
    }
}
