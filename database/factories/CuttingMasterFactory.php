<?php

namespace Database\Factories;

use App\Models\CuttingMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuttingMaster>
 */
class CuttingMasterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'is_active' => true,
        ];
    }
}
