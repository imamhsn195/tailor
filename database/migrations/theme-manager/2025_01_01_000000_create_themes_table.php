<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('themes');
        
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('package')->unique();
            $table->string('version')->default('1.0.0');
            $table->text('description')->nullable();
            $table->boolean('license_required')->default(false);
            $table->boolean('is_active')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('themes');
        Schema::dropIfExists($tableName);
    }
};
