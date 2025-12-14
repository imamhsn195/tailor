<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            [
                'name' => 'Manager',
                'description' => 'Department Manager',
                'is_active' => true,
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Department Supervisor',
                'is_active' => true,
            ],
            [
                'name' => 'Master Tailor',
                'description' => 'Master Tailor',
                'is_active' => true,
            ],
            [
                'name' => 'Tailor',
                'description' => 'Tailor',
                'is_active' => true,
            ],
            [
                'name' => 'Cutter',
                'description' => 'Fabric Cutter',
                'is_active' => true,
            ],
            [
                'name' => 'Helper',
                'description' => 'Helper',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Executive',
                'description' => 'Sales Executive',
                'is_active' => true,
            ],
            [
                'name' => 'Cashier',
                'description' => 'Cashier',
                'is_active' => true,
            ],
            [
                'name' => 'Accountant',
                'description' => 'Accountant',
                'is_active' => true,
            ],
            [
                'name' => 'Designer',
                'description' => 'Fashion Designer',
                'is_active' => true,
            ],
            [
                'name' => 'Embroidery Worker',
                'description' => 'Embroidery Worker',
                'is_active' => true,
            ],
            [
                'name' => 'Quality Controller',
                'description' => 'Quality Controller',
                'is_active' => true,
            ],
        ];

        foreach ($designations as $designation) {
            Designation::updateOrCreate(
                ['name' => $designation['name']],
                $designation
            );
        }
    }
}
