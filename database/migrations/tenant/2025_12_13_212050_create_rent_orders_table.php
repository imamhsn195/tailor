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
        Schema::create('rent_orders', function (Blueprint $table) {
            $table->id();
            $table->string('rent_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->date('rent_date');
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();
            $table->decimal('rent_amount', 10, 2)->default(0);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->string('status')->default('active'); // active, returned, overdue
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
        Schema::dropIfExists('rent_orders');
    }
};
