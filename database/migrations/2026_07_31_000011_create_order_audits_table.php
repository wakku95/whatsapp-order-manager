<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_audits');
    }
};
