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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration')->default(60); // минуты
            $table->decimal('price', 10, 2)->default(0);
            // $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete(); // TODO: создать categories таблицу
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->enum('booking_mode', ['service', 'hotel', 'event', 'online', 'rental', 'chauffeur'])->default('service');
            $table->integer('buffer_time_before')->default(0);
            $table->integer('buffer_time_after')->default(0);
            $table->integer('prepare_time')->default(0);
            $table->integer('max_participants')->nullable();
            $table->integer('min_duration')->nullable();
            $table->integer('max_duration')->nullable();
            $table->boolean('allow_custom_duration')->default(false);
            $table->boolean('allow_recurring')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('booking_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
