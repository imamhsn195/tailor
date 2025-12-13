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
            $table->id();
            $table->string('name');
            $table->string('barcode')->unique()->nullable();
            $table->string('qr_code')->unique()->nullable();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->foreignId('unit_id')->constrained('product_units')->onDelete('restrict');
            $table->string('brand')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('fabric_width', 8, 2)->nullable();
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->string('vat_type')->default('inclusive'); // inclusive, exclusive
            $table->integer('low_stock_alert')->default(0);
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
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
