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
        Schema::table('services', function (Blueprint $table) {
            // Удаляем неиспользуемые поля
            $columnsToDrop = [
                'buffer_time_before',
                'buffer_time_after',
                'prepare_time',
                'max_participants',
                'min_duration',
                'max_duration',
                'allow_custom_duration',
                'allow_recurring',
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Добавляем поле interval, если его еще нет
            if (!Schema::hasColumn('services', 'interval')) {
                $table->integer('interval')->default(15)->after('booking_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Восстанавливаем удаленные поля
            $table->integer('buffer_time_before')->default(0)->after('booking_mode');
            $table->integer('buffer_time_after')->default(0);
            $table->integer('prepare_time')->default(0);
            $table->integer('max_participants')->nullable();
            $table->integer('min_duration')->nullable();
            $table->integer('max_duration')->nullable();
            $table->boolean('allow_custom_duration')->default(false);
            $table->boolean('allow_recurring')->default(false);
            
            // Удаляем interval
            if (Schema::hasColumn('services', 'interval')) {
                $table->dropColumn('interval');
            }
        });
    }
};
