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
        Schema::connection('central')->create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название языка (например: Русский, English)
            $table->string('code', 10)->unique(); // Код языка (например: ru, en, kz)
            $table->boolean('is_active')->default(true); // Активен ли язык
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('languages');
    }
};
