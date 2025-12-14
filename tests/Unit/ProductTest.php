<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\ProductSize;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * Test that a product can be created.
     */
    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'barcode' => '123456789',
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'barcode' => '123456789',
        ]);

        $this->assertEquals('Test Product', $product->name);
    }

    /**
     * Test that product belongs to a category.
     */
    public function test_product_belongs_to_category(): void
    {
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(ProductCategory::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /**
     * Test that product belongs to a unit.
     */
    public function test_product_belongs_to_unit(): void
    {
        $unit = ProductUnit::factory()->create();
        $product = Product::factory()->create(['unit_id' => $unit->id]);

        $this->assertInstanceOf(ProductUnit::class, $product->unit);
        $this->assertEquals($unit->id, $product->unit->id);
    }

    /**
     * Test that product can have sizes.
     */
    public function test_product_can_have_sizes(): void
    {
        $product = Product::factory()->create();
        $size = ProductSize::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($product->sizes->contains($size));
        $this->assertEquals(1, $product->sizes->count());
    }

    /**
     * Test that product prices are cast to decimal.
     */
    public function test_product_prices_are_decimal(): void
    {
        $product = Product::factory()->create([
            'purchase_price' => 100.50,
            'sale_price' => 150.75,
        ]);

        $this->assertIsFloat($product->purchase_price);
        $this->assertIsFloat($product->sale_price);
        $this->assertEquals(100.50, $product->purchase_price);
        $this->assertEquals(150.75, $product->sale_price);
    }

    /**
     * Test that product images is cast to array.
     */
    public function test_product_images_is_array(): void
    {
        $images = ['image1.jpg', 'image2.jpg'];
        $product = Product::factory()->create(['images' => $images]);

        $this->assertIsArray($product->images);
        $this->assertEquals($images, $product->images);
    }

    /**
     * Test that product is_active is cast to boolean.
     */
    public function test_product_is_active_is_boolean(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $this->assertIsBool($product->is_active);
        $this->assertTrue($product->is_active);
    }

    /**
     * Test that product uses soft deletes.
     */
    public function test_product_uses_soft_deletes(): void
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNull(Product::find($productId));
        $this->assertNotNull(Product::withTrashed()->find($productId));
    }
}
