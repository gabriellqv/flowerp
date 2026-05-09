<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['INCOME', 'EXPENSE']);
            $table->decimal('amount', 12, 2);
            $table->string('description', 500);
            $table->string('category', 50)->nullable(); // SALE, PURCHASE, OTHER
            $table->uuid('sale_id')->nullable();
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->index('type');
            $table->index('created_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};
