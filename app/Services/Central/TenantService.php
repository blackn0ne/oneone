<?php

namespace App\Services\Central;

use App\Models\Central\Tenant;
use App\Models\Central\Domain;
use App\Models\Central\Subscription;
use App\Models\Central\Plan;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use Carbon\Carbon;
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

        // Создаем подписку, если выбран тарифный план
        if (!empty($data['plan_id'])) {
            $plan = Plan::find($data['plan_id']);

            if ($plan) {
                $now = Carbon::now();

                // Статус подписки в зависимости от статуса tenant
                $tenantStatus = $data['status'] ?? TenantStatus::TRIAL->value;
                $subscriptionStatus = match ($tenantStatus) {
                    TenantStatus::ACTIVE->value => SubscriptionStatus::ACTIVE->value,
                    TenantStatus::TRIAL->value => SubscriptionStatus::TRIALING->value,
                    default => SubscriptionStatus::ACTIVE->value,
                };

                $startsAt = $now;
                $trialEndsAt = null;
                $endsAt = null;

                if ($subscriptionStatus === SubscriptionStatus::TRIALING->value) {
                    // Длительность пробного периода можно вынести в конфиг
                    $trialDays = (int) config('billing.trial_days', 14);
                    $trialEndsAt = $now->copy()->addDays($trialDays);
                } else {
                    // Окончание периода подписки в зависимости от интервала плана
                    if ($plan->interval === 'monthly') {
                        $endsAt = $now->copy()->addMonth();
                    } elseif ($plan->interval === 'yearly') {
                        $endsAt = $now->copy()->addYear();
                    }
                }

                $tenant->subscription()->create([
                    'plan_id' => $plan->id,
                    'status' => $subscriptionStatus,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'trial_ends_at' => $trialEndsAt,
                ]);
            }
        }

        // Убеждаемся, что база данных создана (если события не сработали)
        $this->ensureDatabaseExists($tenant);

        return $tenant->fresh(['plan', 'subscription']);
    }

    /**
     * Убедиться, что база данных для tenant существует
     *
     * @param Tenant $tenant
     * @return void
     */
    public function ensureDatabaseExists(Tenant $tenant): void
    {
        try {
            // Проверяем, существует ли база данных
            $databaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
            
            $databaseCreated = false;
            $databaseExists = $this->databaseExists($databaseName);
            
            // Пытаемся создать базу, если её нет
            if (!$databaseExists) {
                $tenant->database()->manager()->createDatabase($tenant);
                $databaseCreated = true;
            }

            // Если база только что создана или нужно проверить миграции
            if ($databaseCreated) {
                // Запускаем миграции через Job
                $migrateJob = new MigrateDatabase($tenant);
                $migrateJob->handle($tenant);
            } else {
                // Создаем прямое подключение к tenant базе данных
                $centralConnection = config('tenancy.database.central_connection', 'mysql');
                $config = config("database.connections.{$centralConnection}");
                $config['database'] = $databaseName;
                \Config::set("database.connections.tenant_check", $config);
                $tenantConnection = \DB::connection('tenant_check');
                
                try {
                    // Получаем имя текущей базы данных
                    $currentDatabase = $tenantConnection->select("SELECT DATABASE() as db")[0]->db ?? 'unknown';
                    
                    // Проверяем, сколько реальных таблиц в базе через правильное подключение
                    $tables = $tenantConnection->select("SHOW TABLES");
                    $tableCount = count($tables);
                    
                    $tableNames = [];
                    foreach ($tables as $table) {
                        $tableName = array_values((array)$table)[0];
                        $tableNames[] = $tableName;
                    }
                    
                    // Проверяем наличие таблицы migrations
                    try {
                        $migrationsCount = $tenantConnection->table('migrations')->count();
                    } catch (\Exception $e) {
                        // Игнорируем ошибки
                    }
                    
                    // Проверяем наличие основных таблиц tenant
                    $requiredTables = ['locations', 'services', 'staff', 'customers', 'bookings', 'service_staff', 'business', 'sessions'];
                    $missingTables = [];
                    foreach ($requiredTables as $requiredTable) {
                        if (!in_array($requiredTable, $tableNames)) {
                            $missingTables[] = $requiredTable;
                        }
                    }
                    
                    if (!empty($missingTables)) {
                        // Удаляем частично созданные таблицы, если они есть
                        $partialTables = ['staff', 'locations', 'services', 'customers', 'bookings', 'service_staff', 'business', 'sessions', 'settings'];
                        foreach ($partialTables as $table) {
                            try {
                                $tenantConnection->statement("DROP TABLE IF EXISTS `{$table}`");
                            } catch (\Exception $e) {
                                // Игнорируем ошибки, если таблицы нет
                            }
                        }
                        
                        // Создаем таблицу migrations, если её нет
                        try {
                            $tenantConnection->statement("
                                CREATE TABLE IF NOT EXISTS `migrations` (
                                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                    `migration` varchar(255) NOT NULL,
                                    `batch` int(11) NOT NULL,
                                    PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                            ");
                        } catch (\Exception $e) {
                            // Игнорируем ошибки
                        }
                        
                        // Выполняем миграции напрямую через подключение
                        $migrationPath = database_path('migrations/tenant');
                        
                        // Получаем список файлов миграций
                        $migrationFiles = glob($migrationPath . '/*.php');
                        
                        // Выполняем миграции через прямое подключение
                        $migrator = app('migrator');
                        $migrator->setConnection('tenant_check');
                        
                        // Проверяем, какие миграции уже выполнены
                        $ranMigrations = $migrator->getRepository()->getRan();
                        
                        // Выполняем только новые миграции
                        $migrator->run([$migrationPath], ['--force' => true]);
                        
                        $ranMigrationsAfter = $migrator->getRepository()->getRan();
                        
                        // Запускаем seeder для ролей и разрешений
                        $this->runTenantSeeder($tenantConnection);
                        
                        // Проверяем снова после миграций
                        $tablesAfter = $tenantConnection->select("SHOW TABLES");
                        $tableCountAfter = count($tablesAfter);
                        $tableNamesAfter = [];
                        foreach ($tablesAfter as $table) {
                            $tableName = array_values((array)$table)[0];
                            $tableNamesAfter[] = $tableName;
                        }
                    }
                } catch (\Exception $e) {
                    // Если таблиц нет - запускаем миграции напрямую
                    try {
                        // Создаем таблицу migrations, если её нет
                        try {
                            $tenantConnection->statement("
                                CREATE TABLE IF NOT EXISTS `migrations` (
                                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                    `migration` varchar(255) NOT NULL,
                                    `batch` int(11) NOT NULL,
                                    PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                            ");
                        } catch (\Exception $e3) {
                            // Игнорируем ошибки
                        }
                        
                        $migrationPath = database_path('migrations/tenant');
                        $migrator = app('migrator');
                        $migrator->setConnection('tenant_check');
                        $migrator->run([$migrationPath], ['--force' => true]);
                        
                        // Запускаем seeder для ролей и разрешений
                        $this->runTenantSeeder($tenantConnection);
                        
                        // Проверяем после миграций
                        $tablesAfter = $tenantConnection->select("SHOW TABLES");
                        $tableCountAfter = count($tablesAfter);
                        $tableNamesAfter = [];
                        foreach ($tablesAfter as $table) {
                            $tableName = array_values((array)$table)[0];
                            $tableNamesAfter[] = $tableName;
                        }
                    } catch (\Exception $e2) {
                        // Игнорируем ошибки
                    }
                } finally {
                    // Закрываем временное подключение
                    $tenantConnection->disconnect();
                }
            }
        } catch (\Exception $e) {
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
     * Проверить статус миграций для tenant
     *
     * @param Tenant $tenant
     * @return array
     */
    public function getMigrationStatus(Tenant $tenant): array
    {
        $databaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
        
        // Проверяем, существует ли база данных
        if (!$this->databaseExists($databaseName)) {
            return [
                'database_exists' => false,
                'migrations_completed' => false,
                'table_count' => 0,
                'required_tables' => [],
                'missing_tables' => ['locations', 'services', 'staff', 'customers', 'bookings', 'service_staff', 'business', 'sessions'],
            ];
        }
        
        // Создаем подключение к tenant базе
        $centralConnection = config('tenancy.database.central_connection', 'mysql');
        $config = config("database.connections.{$centralConnection}");
        $config['database'] = $databaseName;
        \Config::set("database.connections.tenant_status", $config);
        $tenantConnection = \DB::connection('tenant_status');
        
        try {
            // Проверяем таблицы
            $tables = $tenantConnection->select("SHOW TABLES");
            $tableCount = count($tables);
            
            $tableNames = [];
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                $tableNames[] = $tableName;
            }
            
            // Проверяем наличие основных таблиц
            $requiredTables = ['locations', 'services', 'staff', 'customers', 'bookings', 'service_staff', 'business', 'sessions'];
            $missingTables = [];
            foreach ($requiredTables as $requiredTable) {
                if (!in_array($requiredTable, $tableNames)) {
                    $missingTables[] = $requiredTable;
                }
            }
            
            return [
                'database_exists' => true,
                'migrations_completed' => empty($missingTables),
                'table_count' => $tableCount,
                'required_tables' => $requiredTables,
                'missing_tables' => $missingTables,
            ];
        } catch (\Exception $e) {
            return [
                'database_exists' => true,
                'migrations_completed' => false,
                'table_count' => 0,
                'required_tables' => [],
                'missing_tables' => ['locations', 'services', 'staff', 'customers', 'bookings', 'service_staff', 'business', 'sessions'],
                'error' => $e->getMessage(),
            ];
        } finally {
            $tenantConnection->disconnect();
        }
    }

    /**
     * Запустить seeder для tenant БД
     *
     * @param \Illuminate\Database\Connection $connection
     * @return void
     */
    protected function runTenantSeeder($connection): void
    {
        try {
            // Сохраняем текущее подключение
            $originalConnection = \DB::getDefaultConnection();
            
            // Устанавливаем подключение для seeder
            \DB::setDefaultConnection($connection->getName());
            
            // Устанавливаем подключение для моделей Spatie Permission
            \Config::set('database.default', $connection->getName());
            
            // Запускаем seeder
            $seeder = new \Database\Seeders\TenantRolePermissionSeeder();
            // Создаем фиктивную команду для seeder
            if (app()->bound('Illuminate\Console\Command')) {
                $seeder->setCommand(app('Illuminate\Console\Command'));
            }
            $seeder->run();
            
            // Восстанавливаем подключение
            \DB::setDefaultConnection($originalConnection);
            \Config::set('database.default', $originalConnection);
        } catch (\Exception $e) {
            // Игнорируем ошибки
        }
    }

    /**
     * Удалить tenant и все связанные данные
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function deleteTenant(Tenant $tenant): bool
    {
        // Пытаемся удалить базу данных tenant
        try {
            $databaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');

            // Менеджер БД tenancy отвечает за фактический DROP DATABASE
            $tenant->database()->manager()->deleteDatabase($tenant);
        } catch (\Throwable $e) {
            // Игнорируем ошибки
        }

        // Удаление всех доменов
        $tenant->domains()->delete();

        // Удаление tenant (база данных будет удалена автоматически через события)
        return $tenant->delete();
    }
}
