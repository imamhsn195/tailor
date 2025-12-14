<?php

namespace Tests\Unit;

use App\Models\ProductCategory;
use App\Models\Product;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    /**
     * Test that a product category can be created.
     */
    public function test_product_category_can_be_created(): void
    {
        $category = ProductCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->assertDatabaseHas('product_categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->assertEquals('Test Category', $category->name);
    }

    /**
     * Test that product category can have products.
     */
    public function test_product_category_can_have_products(): void
    {
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->products->contains($product));
        $this->assertEquals(1, $category->products->count());
    }

    /**
     * Test that product category can have parent category.
     */
    public function test_product_category_can_have_parent(): void
    {
        $parentCategory = ProductCategory::factory()->create();
        $childCategory = ProductCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertInstanceOf(ProductCategory::class, $childCategory->parent);
        $this->assertEquals($parentCategory->id, $childCategory->parent->id);
    }

    /**
     * Test that product category can have child categories.
     */
    public function test_product_category_can_have_children(): void
    {
        $parentCategory = ProductCategory::factory()->create();
        $childCategory = ProductCategory::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertTrue($parentCategory->children->contains($childCategory));
        $this->assertEquals(1, $parentCategory->children->count());
    }

    /**
     * Test that product category sort_order is cast to integer.
     */
    public function test_product_category_sort_order_is_integer(): void
    {
        $category = ProductCategory::factory()->create(['sort_order' => 10]);

        $this->assertIsInt($category->sort_order);
        $this->assertEquals(10, $category->sort_order);
    }

    /**
     * Test that product category is_active is cast to boolean.
     */
    public function test_product_category_is_active_is_boolean(): void
    {
        $category = ProductCategory::factory()->create(['is_active' => true]);

        $this->assertIsBool($category->is_active);
        $this->assertTrue($category->is_active);
    }

    /**
     * Test that product category uses soft deletes.
     */
    public function test_product_category_uses_soft_deletes(): void
    {
        $category = ProductCategory::factory()->create();
        $categoryId = $category->id;

        $category->delete();

        $this->assertSoftDeleted('product_categories', ['id' => $categoryId]);
        $this->assertNull(ProductCategory::find($categoryId));
        $this->assertNotNull(ProductCategory::withTrashed()->find($categoryId));
    }
}
