<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ProvisionTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:provision 
                            {tenant : The tenant ID or domain}
                            {--email= : Admin email}
                            {--name= : Admin name}
                            {--password= : Admin password (will be generated if not provided)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision a new tenant database and setup initial data';

    /**
     * Execute the console command.
     */
    public function handle(TenantProvisioningService $service)
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
        
        if ($tenant->status === 'active') {
            if (!$this->confirm("Tenant is already active. Do you want to re-provision?")) {
                return 0;
            }
        }
        
        $this->info("Provisioning tenant: {$tenant->name} ({$tenant->domain})");
        
        // Prepare admin data
        $adminData = [
            'email' => $this->option('email') ?? $this->ask('Admin email', 'admin@' . $tenant->domain),
            'name' => $this->option('name') ?? $this->ask('Admin name', 'Admin'),
            'password' => $this->option('password') ?? Str::random(12),
            'send_email' => false, // Set to true to send welcome email
        ];
        
        try {
            $service->provision($tenant, $adminData);
            
            $this->info("Tenant provisioned successfully!");
            $this->info("Database: {$tenant->database_name}");
            $this->info("Admin Email: {$adminData['email']}");
            $this->info("Admin Password: {$adminData['password']}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to provision tenant: " . $e->getMessage());
            return 1;
        }
    }
}
