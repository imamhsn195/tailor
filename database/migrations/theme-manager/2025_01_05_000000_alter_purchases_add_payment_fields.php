<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('purchases');
        
        Schema::table($tableName, function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('download_token')->nullable()->after('payment_reference');
            $table->timestamp('download_expires_at')->nullable()->after('download_token');
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('purchases');
        
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'download_token', 'download_expires_at']);
        });
    }
};
