<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CentralUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем или получаем роль super_admin
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Создаем центрального пользователя (суперадмина)
        $centralUser = User::firstOrCreate(
            ['email' => 'admin@oneone.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Назначаем роль super_admin
        if (!$centralUser->hasRole('super_admin')) {
            $centralUser->assignRole('super_admin');
        }

        $this->command->info('Центральный пользователь создан!');
        $this->command->info('Email: admin@oneone.local');
        $this->command->info('Password: password');
        $this->command->info('Роль: super_admin');
    }
}
