<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('central')->table('central_users', function (Blueprint $table) {
            // Добавляем поле role, если его еще нет
            if (!Schema::connection('central')->hasColumn('central_users', 'role')) {
                $table->enum('role', ['super_admin', 'admin', 'staff'])->default('staff')->after('password');
            }
        });

        // Мигрируем данные: если is_super_admin = true, то role = 'super_admin', иначе 'staff'
        DB::connection('central')->table('central_users')->where('is_super_admin', true)->update(['role' => 'super_admin']);
        DB::connection('central')->table('central_users')->where('is_super_admin', false)->whereNull('role')->update(['role' => 'staff']);

        // Удаляем старое поле is_super_admin
        Schema::connection('central')->table('central_users', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('central_users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('central_users', function (Blueprint $table) {
            // Восстанавливаем is_super_admin
            if (!Schema::connection('central')->hasColumn('central_users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('password');
            }
        });

        // Мигрируем данные обратно
        DB::connection('central')->table('central_users')->where('role', 'super_admin')->update(['is_super_admin' => true]);
        DB::connection('central')->table('central_users')->where('role', '!=', 'super_admin')->update(['is_super_admin' => false]);

        // Удаляем поле role
        Schema::connection('central')->table('central_users', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('central_users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
