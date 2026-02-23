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
        Schema::connection('central')->create('settings', function (Blueprint $table) {
            $table->id();
            
            // General settings
            $table->string('project_name')->nullable();
            $table->text('project_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('global_currency', 3)->default('USD');
            $table->string('default_language', 10)->default('ru');
            
            // Payment settings
            $table->boolean('bank_transfer_enabled')->default(false);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_swift')->nullable();
            $table->string('bank_iban')->nullable();
            $table->text('bank_instructions')->nullable();
            
            // Languages (JSON)
            $table->json('languages')->nullable();
            
            // Email SMTP settings
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable(); // tls, ssl, null
            $table->string('smtp_from_address')->nullable();
            $table->string('smtp_from_name')->nullable();
            
            // WhatsApp Business API settings
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('whatsapp_api_key')->nullable();
            $table->text('whatsapp_api_secret')->nullable();
            $table->string('whatsapp_phone_number')->nullable();
            $table->string('whatsapp_business_id')->nullable();
            $table->string('whatsapp_webhook_url')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('settings');
    }
};
