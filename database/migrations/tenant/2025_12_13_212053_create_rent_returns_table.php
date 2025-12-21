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
        Schema::create('rent_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_order_id')->constrained('rent_orders')->onDelete('cascade');
            $table->date('return_date');
            $table->string('return_status')->default('complete'); // complete, partial, damaged
            $table->decimal('damage_charges', 10, 2)->default(0);
            $table->decimal('late_fees', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
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
        Schema::dropIfExists('rent_returns');
    }
};
