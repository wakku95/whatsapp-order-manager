<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('whatsapp_connection_id')->nullable()->constrained('whatsapp_connections')->onDelete('set null');
            $table->string('order_number', 100);
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('delivery_fee', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->decimal('cod_amount', 12, 2)->default(0.00);
            $table->enum('cod_status', ['pending', 'collected', 'failed', 'refunded'])->default('pending');
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'order_number']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
