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
        if (Schema::hasTable('staff')) {
            return;
        }
        
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            // user_id ссылается на users в центральной базе, поэтому foreign key не нужен
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->string('specialization')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('locations')->nullable();
            $table->json('breaks')->nullable();
            $table->json('holidays')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('email');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
