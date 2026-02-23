<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Проверяем, существует ли таблица
        if (!Schema::connection('central')->hasTable('tenants')) {
            Schema::connection('central')->create('tenants', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->enum('status', ['active', 'suspended', 'trial'])->default('trial');
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamps();
                $table->json('data')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenants');
    }
}
