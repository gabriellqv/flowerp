<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('name', 200);
            $table->string('sku', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5);
            $table->boolean('is_active')->default(true);
            $table->index('sku');
            $table->index('category_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
