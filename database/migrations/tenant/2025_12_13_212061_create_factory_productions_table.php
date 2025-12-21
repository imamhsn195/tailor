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
        Schema::create('factory_productions', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->date('production_date');
            $table->integer('quantity')->default(0);
            $table->string('status')->default('pending'); // pending, cutting_received, material_issued, in_progress, quality_inspection, completed, dispatched
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factory_productions');
    }
};
