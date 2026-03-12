<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Создает роли и разрешения для tenant БД
     */
    public function run(): void
    {
        // Используем текущее подключение (должно быть tenant)
        $connection = \DB::getDefaultConnection();
        \Log::info("TenantRolePermissionSeeder: Используется подключение: {$connection}");
        
        // Создаем права доступа для tenant
        $permissions = [
            // Bookings
            'view bookings',
            'create bookings',
            'edit bookings',
            'delete bookings',
            'cancel bookings',
            
            // Services
            'view services',
            'create services',
            'edit services',
            'delete services',
            
            // Staff
            'view staff',
            'create staff',
            'edit staff',
            'delete staff',
            
            // Customers
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Locations
            'view locations',
            'create locations',
            'edit locations',
            'delete locations',
            
            // Settings
            'view settings',
            'edit settings',
            
            // Reports
            'view reports',
            
            // Admin (все права)
            'manage all',
        ];

        // Устанавливаем подключение для моделей Permission и Role
        $connection = \DB::getDefaultConnection();
        
        foreach ($permissions as $permission) {
            Permission::on($connection)->firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Создаем роли для tenant
        $roles = [
            'Мастер' => [
                'name' => 'Мастер',
                'permissions' => [
                    'view bookings', // только свои бронирования
                    'edit bookings', // только статус своих бронирований
                ],
            ],
            'Менеджер' => [
                'name' => 'Менеджер',
                'permissions' => [
                    'view bookings', 'create bookings', 'edit bookings', 'delete bookings', 'cancel bookings',
                    'view services', 'create services', 'edit services', 'delete services',
                    'view staff', 'create staff', 'edit staff', 'delete staff',
                    'view customers', 'create customers', 'edit customers', 'delete customers',
                    'view business', 'create business', 'edit business', 'delete business',
                    // НЕТ: view settings, edit settings, view reports
                ],
            ],
            'Админ' => [
                'name' => 'Админ',
                'permissions' => ['manage all'], // все разрешения
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::on($connection)->firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web']
            );
            
            // Назначаем права
            $permissions = Permission::on($connection)
                ->whereIn('name', $roleData['permissions'])
                ->where('guard_name', 'web')
                ->get();
            $role->syncPermissions($permissions);
        }

        // Выводим информацию только если сидер вызван через artisan
        if ($this->command) {
            $this->command->info('Tenant роли и права созданы успешно!');
            $this->command->info('Роли: ' . implode(', ', array_keys($roles)));
        }
    }
}
