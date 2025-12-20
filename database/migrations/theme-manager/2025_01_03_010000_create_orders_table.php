<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('orders');
        
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('order_number')->unique();
            $table->decimal('total', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('orders');
        Schema::dropIfExists($tableName);
    }
};
