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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Создаем роли для tenant
        $roles = [
            'admin' => [
                'name' => 'admin',
                'permissions' => ['manage all'],
            ],
            'manager' => [
                'name' => 'manager',
                'permissions' => [
                    'view bookings', 'create bookings', 'edit bookings', 'delete bookings', 'cancel bookings',
                    'view services', 'create services', 'edit services', 'delete services',
                    'view staff', 'create staff', 'edit staff', 'delete staff',
                    'view customers', 'create customers', 'edit customers', 'delete customers',
                    'view locations', 'create locations', 'edit locations', 'delete locations',
                    'view settings', 'edit settings',
                    'view reports',
                ],
            ],
            'staff' => [
                'name' => 'staff',
                'permissions' => [
                    'view bookings', 'create bookings', 'edit bookings',
                    'view services',
                    'view customers', 'create customers', 'edit customers',
                    'view locations',
                ],
            ],
            'viewer' => [
                'name' => 'viewer',
                'permissions' => [
                    'view bookings',
                    'view services',
                    'view staff',
                    'view customers',
                    'view locations',
                    'view reports',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['name' => $roleData['name'], 'guard_name' => 'web']);
            
            // Назначаем права
            $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $this->command->info('Tenant роли и права созданы успешно!');
        $this->command->info('Роли: ' . implode(', ', array_keys($roles)));
    }
}
