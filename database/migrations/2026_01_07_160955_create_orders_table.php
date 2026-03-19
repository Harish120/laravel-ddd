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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->string('order_number')->unique();
            $table->string('status')->default('pending');
            $table->decimal('subtotal_amount', 10, 2)->default(0);
            $table->string('subtotal_currency', 3)->default('USD');
            $table->decimal('shipping_cost_amount', 10, 2)->default(0);
            $table->string('shipping_cost_currency', 3)->default('USD');
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->string('tax_currency', 3)->default('USD');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('total_currency', 3)->default('USD');
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('order_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
