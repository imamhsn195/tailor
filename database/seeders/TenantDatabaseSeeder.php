<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tenant Database Seeder
 * This seeder should be run for each tenant database
 */
class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ProductCategorySeeder::class,
            ProductUnitSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            WorkerCategorySeeder::class,
            ChartOfAccountSeeder::class,
        ]);
    }
}
