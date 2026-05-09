<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('seller_id')->constrained('users');
            $table->uuid('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('COMPLETED'); // COMPLETED, CANCELLED
            $table->index('seller_id');
            $table->index('created_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
