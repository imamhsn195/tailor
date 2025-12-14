<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact_person' => fake()->name(),
            'mobile' => fake()->optional()->phoneNumber(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->optional()->address(),
            'vat_no' => fake()->optional()->numerify('VAT#######'),
            'discount_percentage' => fake()->randomFloat(2, 0, 20),
            'total_purchase_amount' => fake()->randomFloat(2, 0, 100000),
            'total_paid_amount' => fake()->randomFloat(2, 0, 100000),
            'total_due_amount' => fake()->randomFloat(2, 0, 50000),
            'is_active' => true,
        ];
    }
}
