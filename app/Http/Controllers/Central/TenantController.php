<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Http\Requests\Central\UpdateTenantRequest;
use App\Models\Central\Tenant;
use App\Models\Central\Plan;
use App\Models\User;
use App\Services\Central\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления tenants (клиентами платформы)
 */
class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {
    }

    /**
     * Отобразить список всех tenants
     *
     * @return Response
     */
    public function index(): Response
    {
        $tenants = Tenant::with(['plan', 'subscription'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Показать форму создания нового tenant
     *
     * @return Response
     */
    public function create(): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Central/Tenants/Create', [
            'plans' => $plans,
        ]);
    }

    /**
     * Сохранить нового tenant
     *
     * @param StoreTenantRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = $this->tenantService->createTenant($request->validated());

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant успешно создан!');
    }

    /**
     * Отобразить детальную информацию о tenant
     *
     * @param Tenant $tenant
     * @return Response
     */
    public function show(Tenant $tenant): Response
    {
        $tenant->load(['plan', 'subscription', 'domains', 'billings']);

        // Проверяем статус миграций
        $migrationStatus = $this->tenantService->getMigrationStatus($tenant);

        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'migrationStatus' => $migrationStatus,
        ]);
    }

    /**
     * Показать форму редактирования tenant
     *
     * @param Tenant $tenant
     * @return Response
     */
    public function edit(Tenant $tenant): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Central/Tenants/Edit', [
            'tenant' => $tenant->load(['plan', 'subscription', 'domains']),
            'plans' => $plans,
        ]);
    }

    /**
     * Обновить tenant
     *
     * @param UpdateTenantRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->tenantService->updateTenant($tenant, $request->validated());

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant обновлен!');
    }

    /**
     * Привязать глобального пользователя к tenant как admin (создать Staff в базе tenant)
     */
    public function attachUser(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        try {
            // Переключаемся на базу данных tenant
            tenancy()->initialize($tenant);

            $tenantDb = \DB::connection('tenant');

            // Используем Query Builder напрямую — гарантированно пишем в БД тенанта
            $exists = $tenantDb->table('staff')->where('user_id', $user->id)->exists();

            if ($exists) {
                $tenantDb->table('staff')->where('user_id', $user->id)->update([
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
                $staff = \App\Models\Tenant\Staff::where('user_id', $user->id)->first();
            } else {
                $staffId = $tenantDb->table('staff')->insertGetId([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $staff = \App\Models\Tenant\Staff::find($staffId);
            }

            // Присваиваем роль admin назначенному администратору
            if ($staff) {
                $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => 'admin', 'guard_name' => 'web']
                );
                $staff->assignRole($adminRole);
            }
        } catch (\Throwable $e) {

            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('error', 'Ошибка при назначении администратора: ' . $e->getMessage());
        } finally {
            // Возвращаемся к центральной базе
            tenancy()->end();
        }

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Пользователь привязан к tenant как администратор.');
    }

    /**
     * Создать базу данных для tenant (если не создана)
     *
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function createDatabase(Tenant $tenant): RedirectResponse
    {
        try {
            $this->tenantService->ensureDatabaseExists($tenant);

            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('success', 'База данных для tenant создана и миграции выполнены!');
        } catch (\Exception $e) {
            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('error', 'Ошибка при создании базы данных: ' . $e->getMessage());
        }
    }

    /**
     * Обновить миграции для tenant
     *
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function updateDatabase(Tenant $tenant): RedirectResponse
    {
        try {
            $databaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
            
            // Создаем подключение к tenant базе
            $centralConnection = config('tenancy.database.central_connection', 'mysql');
            $config = config("database.connections.{$centralConnection}");
            $config['database'] = $databaseName;
            \Config::set("database.connections.tenant_update", $config);
            $tenantConnection = \DB::connection('tenant_update');
            
            try {
                // Убеждаемся, что таблица migrations существует
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
                }
                
                // Путь к миграциям и настройка мигратора
                $migrationPath = database_path('migrations/tenant');
                $migrator = app('migrator');
                $migrator->setConnection('tenant_update');
                $repository = $migrator->getRepository();
                
                // Проверяем, какие миграции уже выполнены
                $ranMigrations = $repository->getRan();
                
                // Получаем список всех файлов миграций
                $migrationFiles = glob($migrationPath . '/*.php');
                $allMigrations = [];
                foreach ($migrationFiles as $file) {
                    $allMigrations[] = basename($file, '.php');
                }
                
                // Получаем список существующих таблиц
                $tables = $tenantConnection->select("SHOW TABLES");
                $tableNames = [];
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    $tableNames[] = $tableName;
                }
                
                // 1) Случай: миграция помечена выполненной, но таблица отсутствует — удаляем запись из migrations,
                // чтобы миграция могла выполниться заново
                $resetMigrations = 0;
                foreach ($ranMigrations as $migrationName) {
                    $migrationFile = $migrationPath . '/' . $migrationName . '.php';
                    if (file_exists($migrationFile)) {
                        $content = file_get_contents($migrationFile);
                        $tableName = null;
                        if (preg_match("/Schema::create\(['\"]([^'\"]+)['\"]/", $content, $matches)) {
                            $tableName = $matches[1];
                        } elseif (preg_match("/->create\(['\"]([^'\"]+)['\"]/", $content, $matches)) {
                            $tableName = $matches[1];
                        }
                        
                        if ($tableName && ! in_array($tableName, $tableNames)) {
                            $repository->delete($migrationName);
                            $resetMigrations++;
                        }
                    }
                }
                
                if ($resetMigrations > 0) {
                    // Обновляем список выполненных миграций после удаления
                    $ranMigrations = $repository->getRan();
                }
                
                // 2) Случай: таблица существует, но миграция не записана — просто логируем её в migrations
                $missingMigrations = array_diff($allMigrations, $ranMigrations);
                
                $batch = $repository->getNextBatchNumber();
                $loggedMigrations = 0;
                foreach ($missingMigrations as $migrationName) {
                    $migrationFile = $migrationPath . '/' . $migrationName . '.php';
                    if (file_exists($migrationFile)) {
                        $content = file_get_contents($migrationFile);
                        // Пытаемся найти имя таблицы в миграции (несколько вариантов)
                        $tableName = null;
                        if (preg_match("/Schema::create\(['\"]([^'\"]+)['\"]/", $content, $matches)) {
                            $tableName = $matches[1];
                        } elseif (preg_match("/->create\(['\"]([^'\"]+)['\"]/", $content, $matches)) {
                            $tableName = $matches[1];
                        }
                        
                        if ($tableName) {
                            try {
                                $tableExists = $tenantConnection->select("SHOW TABLES LIKE '{$tableName}'");
                                if (count($tableExists) > 0) {
                                    // Таблица существует, но миграция не записана - записываем её
                                    $repository->log($migrationName, $batch);
                                    $loggedMigrations++;
                                }
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
                
                // 3) Выполняем только новые миграции (для тех таблиц, которых ещё нет)
                $migrator->run([$migrationPath], ['--force' => true]);
                
                // Проверяем результат
                $ranMigrationsAfter = $migrator->getRepository()->getRan();
                $newMigrations = count($ranMigrationsAfter) - count($ranMigrations);
                
                return redirect()
                    ->route('central.tenants.show', $tenant)
                    ->with('success', $newMigrations > 0 ? "Выполнено {$newMigrations} новых миграций!" : 'Все миграции уже актуальны!');
            } finally {
                $tenantConnection->disconnect();
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('error', 'Ошибка при обновлении миграций: ' . $e->getMessage());
        }
    }

    /**
     * Удалить tenant
     *
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->tenantService->deleteTenant($tenant);

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Tenant удален!');
    }
}
