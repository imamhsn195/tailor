<?php

namespace Database\Seeders;

use App\Models\WorkerCategory;
use Illuminate\Database\Seeder;

class WorkerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Master Tailor',
                'description' => 'Master Tailor Category',
                'daily_wage' => 1500.00,
                'is_active' => true,
            ],
            [
                'name' => 'Senior Tailor',
                'description' => 'Senior Tailor Category',
                'daily_wage' => 1200.00,
                'is_active' => true,
            ],
            [
                'name' => 'Tailor',
                'description' => 'Regular Tailor Category',
                'daily_wage' => 1000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Junior Tailor',
                'description' => 'Junior Tailor Category',
                'daily_wage' => 800.00,
                'is_active' => true,
            ],
            [
                'name' => 'Cutter',
                'description' => 'Fabric Cutter Category',
                'daily_wage' => 900.00,
                'is_active' => true,
            ],
            [
                'name' => 'Helper',
                'description' => 'Helper Category',
                'daily_wage' => 600.00,
                'is_active' => true,
            ],
            [
                'name' => 'Embroidery Worker',
                'description' => 'Embroidery Worker Category',
                'daily_wage' => 1100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Finishing Worker',
                'description' => 'Finishing Worker Category',
                'daily_wage' => 700.00,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            WorkerCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
