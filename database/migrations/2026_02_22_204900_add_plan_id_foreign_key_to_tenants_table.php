<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('central')->table('tenants', function (Blueprint $table) {
            // Добавляем колонки, если их нет
            if (!Schema::connection('central')->hasColumn('tenants', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::connection('central')->hasColumn('tenants', 'email')) {
                $table->string('email')->unique()->after('name');
            }
            if (!Schema::connection('central')->hasColumn('tenants', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::connection('central')->hasColumn('tenants', 'status')) {
                $table->enum('status', ['active', 'suspended', 'trial'])->default('trial')->after('phone');
            }
            if (!Schema::connection('central')->hasColumn('tenants', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('status');
            }
            if (!Schema::connection('central')->hasColumn('tenants', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('plan_id');
            }
            
            // Добавляем foreign key, если plans существует и foreign key еще нет
            if (Schema::connection('central')->hasTable('plans')) {
                $foreignKeys = Schema::connection('central')
                    ->getConnection()
                    ->select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tenants' AND COLUMN_NAME = 'plan_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                
                if (empty($foreignKeys)) {
                    $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });
    }
};
