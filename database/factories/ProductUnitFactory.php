<?php

namespace Database\Factories;

use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Piece', 'Meter', 'Yard', 'Kg', 'Gram', 'Liter']),
            'abbreviation' => fake()->randomElement(['pc', 'm', 'yd', 'kg', 'g', 'l']),
            'is_active' => true,
        ];
    }
}
