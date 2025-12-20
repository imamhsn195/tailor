<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('licenses');
        $themesTable = TablePrefixHelper::getTableName('themes');
        
        Schema::create($tableName, function (Blueprint $table) use ($themesTable) {
            $table->id();
            $table->foreignId('theme_id')->constrained($themesTable);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('license_key')->unique();
            $table->string('domain');
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('licenses');
        Schema::dropIfExists($tableName);
    }
};
