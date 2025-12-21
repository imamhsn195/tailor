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
        Schema::create('rent_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_order_id')->constrained('rent_orders')->onDelete('cascade');
            $table->date('delivery_date');
            $table->string('delivery_status')->default('delivered'); // delivered, partial
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_deliveries');
    }
};
