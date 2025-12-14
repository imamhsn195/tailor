<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * This seeder is for the main database (not tenant databases)
     */
    public function run(): void
    {
        $this->call([
            PaymentGatewaySeeder::class,
            PlanSeeder::class,
        ]);
    }
}
