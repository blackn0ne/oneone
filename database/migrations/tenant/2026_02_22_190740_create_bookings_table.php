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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->enum('booking_mode', ['service', 'hotel', 'event', 'online', 'rental', 'chauffeur'])->default('service');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->integer('duration'); // минуты
            $table->integer('participants_count')->default(1);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_pattern', ['daily', 'weekly', 'monthly', 'custom'])->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->foreignId('parent_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_time', 'end_time']);
            $table->index('status');
            $table->index('customer_id');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
