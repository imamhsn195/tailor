<?php

namespace Database\Factories;

use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Membership',
            'type' => \App\Enums\MembershipType::GENERAL,
            'description' => fake()->optional()->sentence(),
            'discount_percentage' => fake()->randomFloat(2, 5, 30),
            'is_active' => true,
        ];
    }
}
