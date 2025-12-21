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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('worker_id')->unique();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->string('nid_no')->nullable();
            $table->string('nid_photo')->nullable();
            $table->string('mobile_1')->nullable();
            $table->string('mobile_2')->nullable();
            $table->string('mobile_3')->nullable();
            $table->string('home_mobile_1')->nullable();
            $table->string('home_mobile_2')->nullable();
            $table->string('home_mobile_3')->nullable();
            $table->string('reference_1')->nullable();
            $table->string('reference_2')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('worker_categories')->onDelete('set null');
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
        Schema::dropIfExists('workers');
    }
};
