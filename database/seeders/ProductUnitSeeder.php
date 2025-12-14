<?php

namespace Database\Seeders;

use App\Models\ProductUnit;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Piece',
                'abbreviation' => 'PCS',
                'description' => 'Single piece unit',
                'is_active' => true,
            ],
            [
                'name' => 'Meter',
                'abbreviation' => 'M',
                'description' => 'Meter unit for fabric',
                'is_active' => true,
            ],
            [
                'name' => 'Yard',
                'abbreviation' => 'YD',
                'description' => 'Yard unit for fabric',
                'is_active' => true,
            ],
            [
                'name' => 'Feet',
                'abbreviation' => 'FT',
                'description' => 'Feet unit',
                'is_active' => true,
            ],
            [
                'name' => 'Kilogram',
                'abbreviation' => 'KG',
                'description' => 'Kilogram unit',
                'is_active' => true,
            ],
            [
                'name' => 'Gram',
                'abbreviation' => 'G',
                'description' => 'Gram unit',
                'is_active' => true,
            ],
            [
                'name' => 'Dozen',
                'abbreviation' => 'DZ',
                'description' => 'Dozen unit',
                'is_active' => true,
            ],
            [
                'name' => 'Set',
                'abbreviation' => 'SET',
                'description' => 'Set unit',
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            ProductUnit::updateOrCreate(
                ['abbreviation' => $unit['abbreviation']],
                $unit
            );
        }
    }
}
