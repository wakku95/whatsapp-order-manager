<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('phone_number_id');
            $table->string('waba_id')->nullable();
            $table->string('display_phone_number', 50)->nullable();
            $table->text('access_token');
            $table->text('app_secret')->nullable();
            $table->string('verify_token');
            $table->enum('status', ['active', 'disconnected', 'error'])->default('active');
            $table->timestamps();

            $table->unique(['business_id', 'phone_number_id']);
            $table->index('phone_number_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_connections');
    }
};
