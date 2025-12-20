<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('order_items');
        $ordersTable = TablePrefixHelper::getTableName('orders');
        $productsTable = TablePrefixHelper::getTableName('products');
        
        Schema::create($tableName, function (Blueprint $table) use ($ordersTable, $productsTable) {
            $table->id();
            $table->foreignId('order_id')->constrained($ordersTable)->cascadeOnDelete();
            $table->foreignId('product_id')->constrained($productsTable);
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('order_items');
        Schema::dropIfExists($tableName);
    }
};
