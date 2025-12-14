<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'barcode' => fake()->optional()->unique()->ean13(),
            'qr_code' => fake()->optional()->unique()->uuid(),
            'category_id' => ProductCategory::factory(),
            'unit_id' => ProductUnit::factory(),
            'brand' => fake()->optional()->company(),
            'purchase_price' => fake()->randomFloat(2, 10, 1000),
            'sale_price' => fake()->randomFloat(2, 20, 1500),
            'fabric_width' => fake()->optional()->randomFloat(2, 30, 60),
            'vat_percentage' => fake()->randomFloat(2, 0, 20),
            'vat_type' => fake()->randomElement(['inclusive', 'exclusive']),
            'low_stock_alert' => fake()->numberBetween(0, 100),
            'description' => fake()->optional()->paragraph(),
            'images' => fake()->optional()->randomElements([
                'image1.jpg',
                'image2.jpg',
                'image3.jpg'
            ], fake()->numberBetween(0, 3)),
            'is_active' => true,
        ];
    }
}
