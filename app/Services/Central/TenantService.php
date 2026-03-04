<?php

namespace App\Services\Central;

use App\Models\Central\Tenant;
use App\Models\Central\Domain;
use Illuminate\Support\Str;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Stancl\Tenancy\Tenancy;

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

        // Убеждаемся, что база данных создана (если события не сработали)
        $this->ensureDatabaseExists($tenant);

        return $tenant;
    }

    /**
     * Убедиться, что база данных для tenant существует
     *
     * @param Tenant $tenant
     * @return void
     */
    public function ensureDatabaseExists(Tenant $tenant): void
    {
        \Log::info("ensureDatabaseExists: Начало для tenant {$tenant->id}");
        
        try {
            // Проверяем, существует ли база данных
            $databaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
            \Log::info("ensureDatabaseExists: Проверяем базу данных '{$databaseName}'");
            
            $databaseCreated = false;
            $databaseExists = $this->databaseExists($databaseName);
            \Log::info("ensureDatabaseExists: База данных существует: " . ($databaseExists ? 'да' : 'нет'));
            
            // Пытаемся создать базу, если её нет
            if (!$databaseExists) {
                \Log::info("ensureDatabaseExists: Создаем базу данных '{$databaseName}'");
                $tenant->database()->manager()->createDatabase($tenant);
                $databaseCreated = true;
                \Log::info("ensureDatabaseExists: База данных '{$databaseName}' создана");
            }

            // Если база только что создана или нужно проверить миграции
            if ($databaseCreated) {
                \Log::info("ensureDatabaseExists: Запускаем миграции для только что созданной базы");
                // Запускаем миграции через Job
                $migrateJob = new MigrateDatabase($tenant);
                $migrateJob->handle($tenant);
                \Log::info("ensureDatabaseExists: Миграции выполнены");
            } else {
                \Log::info("ensureDatabaseExists: База уже существует, проверяем наличие таблиц");
                // Проверяем, есть ли таблицы (миграции)
                // Инициализируем tenant подключение
                tenancy()->initialize($tenant);
                
                // Проверяем подключение напрямую к tenant базе
                $tenantConnection = \DB::connection('tenant');
                try {
                    // Получаем имя текущей базы данных через tenant подключение
                    $currentDatabase = $tenantConnection->select("SELECT DATABASE() as db")[0]->db ?? 'unknown';
                    \Log::info("ensureDatabaseExists: Текущая база данных: {$currentDatabase}");
                    \Log::info("ensureDatabaseExists: Ожидаемая база данных: {$databaseName}");
                    
                    // Если подключение не к той базе, переключаемся напрямую
                    if ($currentDatabase !== $databaseName) {
                        \Log::warning("ensureDatabaseExists: Подключение не к той базе! Переключаемся на {$databaseName}");
                        // Используем прямое подключение к tenant базе
                        $centralConnection = config('tenancy.database.central_connection', 'mysql');
                        $config = config("database.connections.{$centralConnection}");
                        $config['database'] = $databaseName;
                        \Config::set("database.connections.tenant_temp", $config);
                        $tenantConnection = \DB::connection('tenant_temp');
                    }
                    
                    // Проверяем, сколько реальных таблиц в базе через правильное подключение
                    $tables = $tenantConnection->select("SHOW TABLES");
                    $tableCount = count($tables);
                    \Log::info("ensureDatabaseExists: Всего таблиц в базе: {$tableCount}");
                    
                    // Логируем список таблиц
                    $tableNames = [];
                    foreach ($tables as $table) {
                        $tableName = array_values((array)$table)[0];
                        $tableNames[] = $tableName;
                    }
                    \Log::info("ensureDatabaseExists: Список таблиц: " . implode(', ', $tableNames));
                    
                    // Проверяем наличие таблицы migrations
                    try {
                        $migrationsCount = $tenantConnection->table('migrations')->count();
                        \Log::info("ensureDatabaseExists: Таблица migrations найдена, записей: {$migrationsCount}");
                    } catch (\Exception $e) {
                        \Log::info("ensureDatabaseExists: Таблица migrations не найдена");
                    }
                    
                    // Проверяем наличие основных таблиц tenant
                    $requiredTables = ['locations', 'services', 'staff', 'customers', 'bookings', 'service_staff'];
                    $missingTables = [];
                    foreach ($requiredTables as $requiredTable) {
                        if (!in_array($requiredTable, $tableNames)) {
                            $missingTables[] = $requiredTable;
                        }
                    }
                    
                    if (!empty($missingTables)) {
                        \Log::info("ensureDatabaseExists: Отсутствуют таблицы: " . implode(', ', $missingTables));
                        \Log::info("ensureDatabaseExists: Запускаем миграции");
                        $migrateJob = new MigrateDatabase($tenant);
                        $migrateJob->handle($tenant);
                        
                        // Проверяем снова после миграций
                        $tablesAfter = $tenantConnection->select("SHOW TABLES");
                        $tableCountAfter = count($tablesAfter);
                        $tableNamesAfter = [];
                        foreach ($tablesAfter as $table) {
                            $tableName = array_values((array)$table)[0];
                            $tableNamesAfter[] = $tableName;
                        }
                        \Log::info("ensureDatabaseExists: После миграций таблиц в базе: {$tableCountAfter}");
                        \Log::info("ensureDatabaseExists: Список таблиц после миграций: " . implode(', ', $tableNamesAfter));
                    } else {
                        \Log::info("ensureDatabaseExists: Все необходимые таблицы присутствуют ({$tableCount}), миграции не требуются");
                    }
                } catch (\Exception $e) {
                    \Log::info("ensureDatabaseExists: Ошибка при проверке таблиц, запускаем миграции. Ошибка: " . $e->getMessage());
                    // Если таблиц нет - запускаем миграции
                    $migrateJob = new MigrateDatabase($tenant);
                    $migrateJob->handle($tenant);
                    
                    // Проверяем после миграций
                    try {
                        $tablesAfter = \DB::select("SHOW TABLES");
                        $tableCountAfter = count($tablesAfter);
                        \Log::info("ensureDatabaseExists: После миграций таблиц в базе: {$tableCountAfter}");
                    } catch (\Exception $e2) {
                        \Log::error("ensureDatabaseExists: Не удалось проверить таблицы после миграций: " . $e2->getMessage());
                    }
                    \Log::info("ensureDatabaseExists: Миграции выполнены");
                } finally {
                    tenancy()->end();
                }
            }
            
            \Log::info("ensureDatabaseExists: Успешно завершено для tenant {$tenant->id}");
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            \Log::error("ensureDatabaseExists: Ошибка для tenant {$tenant->id}: " . $e->getMessage());
            \Log::error("ensureDatabaseExists: Stack trace: " . $e->getTraceAsString());
            throw $e; // Пробрасываем исключение, чтобы контроллер мог его обработать
        }
    }

    /**
     * Проверить, существует ли база данных
     *
     * @param string $databaseName
     * @return bool
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
