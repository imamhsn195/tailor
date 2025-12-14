<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Cutting',
                'description' => 'Fabric cutting department',
                'is_active' => true,
            ],
            [
                'name' => 'Sewing',
                'description' => 'Sewing and stitching department',
                'is_active' => true,
            ],
            [
                'name' => 'Finishing',
                'description' => 'Finishing and quality control department',
                'is_active' => true,
            ],
            [
                'name' => 'Embroidery',
                'description' => 'Embroidery department',
                'is_active' => true,
            ],
            [
                'name' => 'Design',
                'description' => 'Design and pattern making department',
                'is_active' => true,
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales and customer service department',
                'is_active' => true,
            ],
            [
                'name' => 'Administration',
                'description' => 'Administrative department',
                'is_active' => true,
            ],
            [
                'name' => 'Accounts',
                'description' => 'Accounts and finance department',
                'is_active' => true,
            ],
            [
                'name' => 'HR',
                'description' => 'Human resources department',
                'is_active' => true,
            ],
            [
                'name' => 'Store',
                'description' => 'Store and inventory management department',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['name' => $department['name']],
                $department
            );
        }
    }
}
