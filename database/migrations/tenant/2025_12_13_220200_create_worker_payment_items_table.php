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
        Schema::create('worker_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_payment_id')->constrained('worker_payments')->onDelete('cascade');
            $table->foreignId('job_assignment_id')->nullable()->constrained('job_assignments')->onDelete('set null');
            $table->string('product_name')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->date('assign_date')->nullable();
            $table->date('receive_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_payment_items');
    }
};

