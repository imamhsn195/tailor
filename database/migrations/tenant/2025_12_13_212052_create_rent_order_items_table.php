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
        Schema::create('rent_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_order_id')->constrained('rent_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->onDelete('set null');
            $table->string('barcode')->nullable();
            $table->string('product_name');
            $table->string('size')->nullable();
            $table->decimal('rent_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_order_items');
    }
};

