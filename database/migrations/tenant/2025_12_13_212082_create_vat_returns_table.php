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
        Schema::create('vat_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->date('return_date');
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('tailoring_vat', 15, 2)->default(0);
            $table->decimal('pos_sale_vat', 15, 2)->default(0);
            $table->decimal('sherwani_rent_vat', 15, 2)->default(0);
            $table->decimal('total_output_vat', 15, 2)->default(0);
            $table->decimal('total_input_vat', 15, 2)->default(0);
            $table->decimal('vat_payable', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, paid
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
        Schema::dropIfExists('vat_returns');
    }
};

