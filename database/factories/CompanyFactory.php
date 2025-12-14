<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
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
            'branch_name' => fake()->optional()->companySuffix(),
            'address' => fake()->address(),
            'invoice_name' => fake()->optional()->company(),
            'phone' => fake()->optional()->phoneNumber(),
            'mobile' => fake()->optional()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'email' => fake()->optional()->safeEmail(),
            'company_registration_no' => fake()->optional()->numerify('REG#######'),
            'company_tin_no' => fake()->optional()->numerify('TIN#######'),
            'e_bin' => fake()->optional()->numerify('##########'),
            'bin' => fake()->optional()->numerify('##########'),
            'settings' => [
                'currency' => fake()->randomElement(['USD', 'BDT', 'EUR']),
                'timezone' => fake()->randomElement(['UTC', 'Asia/Dhaka', 'America/New_York']),
            ],
        ];
    }
}
