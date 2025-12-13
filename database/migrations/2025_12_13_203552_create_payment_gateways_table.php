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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // stripe, paddle, sslcommerz, aamarpay, shurjopay
            $table->string('display_name');
            $table->string('type')->default('international'); // international, local
            $table->json('credentials')->nullable(); // Encrypted credentials
            $table->json('supported_methods')->nullable(); // bKash, Rocket, Nagad, Card, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
