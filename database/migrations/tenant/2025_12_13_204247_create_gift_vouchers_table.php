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
        Schema::create('gift_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_code')->unique();
            $table->string('name')->nullable();
            $table->decimal('amount', 10, 2);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->date('issued_date');
            $table->date('expiry_date')->nullable();
            $table->date('used_date')->nullable();
            $table->string('status')->default('active'); // active, used, expired
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_vouchers');
    }
};
