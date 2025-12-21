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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->foreignId('department_id')->constrained('departments')->onDelete('restrict');
            $table->foreignId('designation_id')->constrained('designations')->onDelete('restrict');
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nid_no')->nullable();
            $table->string('nid_photo')->nullable();
            $table->string('photo')->nullable();
            $table->string('signature')->nullable();
            $table->string('gender')->nullable(); // male, female, others
            $table->string('type')->default('probationary'); // probationary, permanent
            $table->string('marital_status')->nullable(); // single, married, divorced
            $table->text('study_history')->nullable();
            $table->string('shift')->nullable(); // morning, evening, night
            $table->date('joining_date');
            $table->string('blood_group')->nullable();
            $table->string('religion')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->integer('leave_days')->default(0);
            $table->decimal('allowance', 10, 2)->default(0);
            $table->decimal('loan', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->integer('sewing_limit_yearly')->nullable();
            $table->decimal('sewing_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
