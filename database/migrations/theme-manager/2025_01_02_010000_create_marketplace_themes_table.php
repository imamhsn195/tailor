<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ImamHasan\ThemeManager\Helpers\TablePrefixHelper;

return new class extends Migration {
    public function up(): void
    {
        $tableName = TablePrefixHelper::getTableName('marketplace_themes');
        $categoriesTable = TablePrefixHelper::getTableName('marketplace_categories');
        
        Schema::create($tableName, function (Blueprint $table) use ($categoriesTable) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('preview_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('package_name')->nullable();
            $table->string('version')->default('1.0.0');
            $table->foreignId('author_id')->nullable()->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained($categoriesTable);
            $table->json('tags')->nullable();
            $table->json('features')->nullable();
            $table->integer('sales_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->enum('status', ['draft', 'pending', 'published', 'archived'])->default('draft');
            $table->text('download_url')->nullable();
            $table->boolean('license_required')->default(true);
            $table->json('license_types')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = TablePrefixHelper::getTableName('marketplace_themes');
        Schema::dropIfExists($tableName);
    }
};
