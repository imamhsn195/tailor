<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder is for the main database (not tenant)
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small tailor shops',
                'price_usd' => 9.99,
                'price_bdt' => 999.00,
                'billing_cycle' => 'monthly',
                'trial_days' => 7,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Ideal for growing businesses',
                'price_usd' => 19.99,
                'price_bdt' => 1999.00,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large operations',
                'price_usd' => 39.99,
                'price_bdt' => 3999.00,
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Starter Annual',
                'slug' => 'starter-annual',
                'description' => 'Starter plan with annual billing (2 months free)',
                'price_usd' => 99.90,
                'price_bdt' => 9990.00,
                'billing_cycle' => 'yearly',
                'trial_days' => 7,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Professional Annual',
                'slug' => 'professional-annual',
                'description' => 'Professional plan with annual billing (2 months free)',
                'price_usd' => 199.90,
                'price_bdt' => 19990.00,
                'billing_cycle' => 'yearly',
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Enterprise Annual',
                'slug' => 'enterprise-annual',
                'description' => 'Enterprise plan with annual billing (2 months free)',
                'price_usd' => 399.90,
                'price_bdt' => 39990.00,
                'billing_cycle' => 'yearly',
                'trial_days' => 30,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
