<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('purchases');
        $marketplaceThemesTable = TablePrefixHelper::getTableName('marketplace_themes');
        $licensesTable = TablePrefixHelper::getTableName('licenses');
        
        Schema::create($tableName, function (Blueprint $table) use ($marketplaceThemesTable, $licensesTable) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('marketplace_theme_id')->constrained($marketplaceThemesTable);
            $table->string('order_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_method', ['stripe', 'paypal', 'ngenius', 'bank_transfer', 'cod', 'manual'])->nullable();
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('license_id')->nullable()->constrained($licensesTable);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('purchases');
        Schema::dropIfExists($tableName);
    }
};
