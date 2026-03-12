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
        if (Schema::hasTable('working_hours')) {
            return;
        }

        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('business')->onDelete('cascade');
            $table->string('day_of_week'); // monday, tuesday, wednesday, thursday, friday, saturday, sunday
            $table->boolean('is_closed')->default(false);
            $table->time('start')->default('08:00');
            $table->time('end')->default('22:00');
            $table->timestamps();

            $table->unique(['business_id', 'day_of_week']);
            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
