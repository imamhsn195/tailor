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
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('customer_mobile')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable(); // cash, bkash, rocket, bank, atm
            $table->string('sender_mobile')->nullable(); // For bKash/Rocket
            $table->string('account_number')->nullable(); // For bank transfer
            $table->string('card_last_4')->nullable(); // For ATM card
            $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('sale_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
