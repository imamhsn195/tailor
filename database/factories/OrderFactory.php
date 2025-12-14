<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('ORD##??'),
            'branch_id' => Branch::factory(),
            'customer_id' => Customer::factory(),
            'order_date' => fake()->date(),
            'delivery_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'net_payable' => fake()->randomFloat(2, 100, 10000),
            'paid_amount' => fake()->randomFloat(2, 0, 10000),
            'due_amount' => fake()->randomFloat(2, 0, 10000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
