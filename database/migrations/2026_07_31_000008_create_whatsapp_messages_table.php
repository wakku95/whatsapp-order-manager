<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('whatsapp_connection_id')->constrained('whatsapp_connections')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('provider_message_id');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('message_type', 50)->default('text');
            $table->text('content')->nullable();
            $table->enum('status', ['received', 'processing', 'processed', 'failed', 'sent', 'delivered', 'read'])->default('received');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'provider_message_id']);
            $table->index('whatsapp_connection_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
