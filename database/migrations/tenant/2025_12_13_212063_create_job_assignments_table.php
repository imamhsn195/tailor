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
        Schema::create('job_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->onDelete('restrict');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('cascade');
            $table->foreignId('alteration_id')->nullable()->constrained('alterations')->onDelete('cascade');
            $table->date('assign_date');
            $table->date('expected_receive_date');
            $table->integer('quantity')->default(1);
            $table->decimal('production_charge', 10, 2)->default(0);
            $table->string('status')->default('assigned'); // assigned, in_progress, completed, received
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_assignments');
    }
};
