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
        Schema::table('users', function (Blueprint $table) {
            // Добавляем поле role, если его еще нет
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['super_admin', 'admin', 'staff'])->default('staff')->after('password');
            }
        });

        // Мигрируем данные: если is_super_admin = true, то role = 'super_admin', иначе 'staff'
        DB::table('users')->where('is_super_admin', true)->update(['role' => 'super_admin']);
        DB::table('users')->where('is_super_admin', false)->whereNull('role')->update(['role' => 'staff']);

        // Удаляем старое поле is_super_admin
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Восстанавливаем is_super_admin
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('password');
            }
        });

        // Мигрируем данные обратно
        DB::table('users')->where('role', 'super_admin')->update(['is_super_admin' => true]);
        DB::table('users')->where('role', '!=', 'super_admin')->update(['is_super_admin' => false]);

        // Удаляем поле role
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
