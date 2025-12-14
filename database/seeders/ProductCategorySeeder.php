<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Shirt',
                'slug' => 'shirt',
                'description' => 'Men\'s and Women\'s Shirts',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Pant',
                'slug' => 'pant',
                'description' => 'Men\'s and Women\'s Pants',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Sherwani',
                'slug' => 'sherwani',
                'description' => 'Traditional Sherwani',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Kurta',
                'slug' => 'kurta',
                'description' => 'Traditional Kurta',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Panjabi',
                'slug' => 'panjabi',
                'description' => 'Traditional Panjabi',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Suit',
                'slug' => 'suit',
                'description' => 'Formal Suits',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Blazer',
                'slug' => 'blazer',
                'description' => 'Blazers and Jackets',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Waistcoat',
                'slug' => 'waistcoat',
                'description' => 'Waistcoats',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Fabric',
                'slug' => 'fabric',
                'description' => 'Raw Fabrics',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Tailoring Accessories',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
