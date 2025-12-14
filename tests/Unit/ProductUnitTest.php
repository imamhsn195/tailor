<?php

namespace Tests\Unit;

use App\Models\ProductUnit;
use App\Models\Product;
use Tests\TestCase;

class ProductUnitTest extends TestCase
{
    /**
     * Test that a product unit can be created.
     */
    public function test_product_unit_can_be_created(): void
    {
        $unit = ProductUnit::factory()->create([
            'name' => 'Piece',
            'abbreviation' => 'pc',
        ]);

        $this->assertDatabaseHas('product_units', [
            'name' => 'Piece',
            'abbreviation' => 'pc',
        ]);

        $this->assertEquals('Piece', $unit->name);
        $this->assertEquals('pc', $unit->abbreviation);
    }

    /**
     * Test that product unit can have products.
     */
    public function test_product_unit_can_have_products(): void
    {
        $unit = ProductUnit::factory()->create();
        $product = Product::factory()->create(['unit_id' => $unit->id]);

        $this->assertTrue($unit->products->contains($product));
        $this->assertEquals(1, $unit->products->count());
    }

    /**
     * Test that product unit is_active is cast to boolean.
     */
    public function test_product_unit_is_active_is_boolean(): void
    {
        $unit = ProductUnit::factory()->create(['is_active' => true]);

        $this->assertIsBool($unit->is_active);
        $this->assertTrue($unit->is_active);
    }
}
