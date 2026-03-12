<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('staff', 'business_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->foreignId('business_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('business')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('staff', 'business_id')) {
            Schema::table('staff', function (Blueprint $table) {
                // Пытаемся удалить внешний ключ, игнорируем ошибки если его нет
                try {
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'staff' 
                        AND COLUMN_NAME = 'business_id'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    if (!empty($foreignKeys)) {
                        $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
                        DB::statement("ALTER TABLE `staff` DROP FOREIGN KEY `{$constraintName}`");
                    }
                } catch (\Exception $e) {
                    // Игнорируем ошибки при удалении внешнего ключа
                }
                
                $table->dropColumn('business_id');
            });
        }
    }
};
