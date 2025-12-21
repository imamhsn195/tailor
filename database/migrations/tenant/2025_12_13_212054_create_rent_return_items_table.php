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
        Schema::create('rent_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_return_id')->constrained('rent_returns')->onDelete('cascade');
            $table->foreignId('rent_order_item_id')->constrained('rent_order_items')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->onDelete('set null');
            $table->string('condition')->default('good'); // good, damaged, lost
            $table->decimal('damage_charges', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_return_items');
    }
};

