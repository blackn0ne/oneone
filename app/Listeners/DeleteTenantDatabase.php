<?php

namespace App\Listeners;

use App\Services\Central\TenantService;
use Stancl\Tenancy\Events\TenantDeleted;
use Stancl\Tenancy\Jobs\DeleteDatabase;

class DeleteTenantDatabase
{
    public function __construct(
        protected TenantService $tenantService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(TenantDeleted $event): void
    {
        $tenant = $event->tenant;
        $databaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
        
        // Проверяем существование базы данных перед удалением
        if ($this->databaseExists($databaseName)) {
            try {
                $job = new DeleteDatabase($tenant);
                $job->handle($tenant);
            } catch (\Throwable $e) {
                // Игнорируем ошибки удаления базы данных
            }
        }
    }

    /**
     * Проверить, существует ли база данных
     */
    protected function databaseExists(string $databaseName): bool
    {
        try {
            $connection = config('tenancy.database.central_connection', 'mysql');
            $result = \DB::connection($connection)
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
