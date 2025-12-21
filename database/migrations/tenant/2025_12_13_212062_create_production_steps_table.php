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
        Schema::create('production_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_production_id')->constrained('factory_productions')->onDelete('cascade');
            $table->string('step_name'); // cutting_receive, material_issue, job_issue, job_transfer, quality_inspection, buttonhole, button_attach, iron_finishing, job_receive, dispatch
            $table->date('step_date');
            $table->integer('quantity')->default(0);
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('set null');
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
        Schema::dropIfExists('production_steps');
    }
};
