<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->enum('type', ['IN', 'OUT']);
            $table->integer('quantity');
            $table->string('reason', 50); // INITIAL_STOCK, SALE, PURCHASE, ADJUSTMENT
            $table->index('product_id');
            $table->index('created_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
