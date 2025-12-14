<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed 
                            {tenant : The tenant ID or domain}
                            {--class=TenantDatabaseSeeder : The seeder class to run}
                            {--force : Force the operation to run even in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a tenant database with default data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantIdentifier = $this->argument('tenant');
        
        // Find tenant by ID or domain
        $tenant = is_numeric($tenantIdentifier)
            ? Tenant::find($tenantIdentifier)
            : Tenant::where('domain', $tenantIdentifier)->first();
        
        if (!$tenant) {
            $this->error("Tenant not found: {$tenantIdentifier}");
            return 1;
        }
        
        if ($tenant->status !== 'active') {
            $this->warn("Tenant status is: {$tenant->status}. Proceeding anyway...");
        }
        
        $this->info("Seeding tenant: {$tenant->name} ({$tenant->domain})");
        $this->info("Database: {$tenant->database_name}");
        
        $seederClass = $this->option('class');
        
        try {
            // Make tenant current
            $tenant->makeCurrent();
            
            try {
                // Run the seeder
                $this->info("Running seeder: {$seederClass}");
                
                Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--database' => 'tenant',
                    '--force' => $this->option('force'),
                ], $this->getOutput());
                
                $this->info("Tenant seeded successfully!");
                
                return 0;
            } finally {
                // Always forget current tenant
                Tenant::forgetCurrent();
            }
            
        } catch (\Exception $e) {
            $this->error("Failed to seed tenant: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
