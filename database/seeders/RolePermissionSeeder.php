<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем права доступа
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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Создаем роли
        $roles = [
            'super_admin' => [
                'name' => 'super_admin',
                'description' => 'Суперадмин платформы (управление tenants)',
                'permissions' => ['manage all'],
            ],
            'admin' => [
                'name' => 'admin',
                'description' => 'Полный доступ ко всем функциям tenant',
                'permissions' => ['manage all'],
            ],
            'manager' => [
                'name' => 'manager',
                'description' => 'Управление бронированиями, услугами, сотрудниками и клиентами',
                'permissions' => [
                    'view bookings', 'create bookings', 'edit bookings', 'delete bookings', 'cancel bookings',
                    'view services', 'create services', 'edit services', 'delete services',
                    'view staff', 'create staff', 'edit staff', 'delete staff',
                    'view customers', 'create customers', 'edit customers', 'delete customers',
                    'view locations', 'create locations', 'edit locations', 'delete locations',
                    'view reports',
                ],
            ],
            'staff' => [
                'name' => 'staff',
                'description' => 'Просмотр и создание бронирований, просмотр услуг и клиентов',
                'permissions' => [
                    'view bookings', 'create bookings', 'edit bookings',
                    'view services',
                    'view customers', 'create customers', 'edit customers',
                    'view locations',
                ],
            ],
            'viewer' => [
                'name' => 'viewer',
                'description' => 'Только просмотр данных',
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
            $role = Role::firstOrCreate(['name' => $roleData['name']]);
            
            // Назначаем права
            $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $this->command->info('Роли и права созданы успешно!');
        $this->command->info('Роли: ' . implode(', ', array_keys($roles)));
    }
}
