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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->text('description');
            $table->decimal('price_amount', 10, 2);
            $table->string('price_currency', 3)->default('USD');
            $table->integer('stock_quantity')->default(0);
            $table->string('status')->default('draft');
            $table->uuid('category_id')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index('sku');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
