<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => fake()->unique()->bothify('BR##??'),
            'name' => fake()->company() . ' Branch',
            'e_bin' => fake()->optional()->numerify('##########'),
            'bin' => fake()->optional()->numerify('##########'),
            'address' => fake()->address(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'trade_license_no' => fake()->optional()->numerify('TL#######'),
            'modules' => fake()->randomElements(['pos', 'inventory', 'orders', 'tailor'], fake()->numberBetween(1, 4)),
            'is_active' => true,
        ];
    }
}
