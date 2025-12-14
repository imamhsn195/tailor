<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

trait WithTenant
{
    /**
     * Create and set up a test tenant
     */
    protected function setUpTenant(): Tenant
    {
        // For testing, we use the same database connection (sqlite :memory:)
        // Create a test tenant on the landlord connection
        $tenant = Tenant::on('landlord')->firstOrCreate([
            'domain' => 'test',
        ], [
            'name' => 'Test Tenant',
            'database_name' => ':memory:', // Use same database for tests
            'status' => 'active',
        ]);

        // Create a test plan on landlord connection
        $plan = Plan::on('landlord')->firstOrCreate([
            'slug' => 'test-plan',
        ], [
            'name' => 'Test Plan',
            'price_usd' => 0,
            'price_bdt' => 0,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        // Create an active subscription on landlord connection
        Subscription::on('landlord')->firstOrCreate([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ], [
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        // For tests, configure tenant connection to use the same database
        $testDb = config('database.connections.sqlite.database', ':memory:');
        
        // Update tenant's database_name to match test database
        $tenant->update(['database_name' => $testDb]);
        
        // Configure tenant connection to use the same database as tests
        config([
            'multitenancy.tenant_database_connection_name' => 'sqlite',
            'database.connections.tenant' => array_merge(
                config('database.connections.sqlite', []),
                ['database' => $testDb]
            ),
        ]);
        
        // Make tenant current - this will use the configured tenant connection
        // which points to the same database as our tests
        $tenant->makeCurrent();

        return $tenant;
    }

    /**
     * Clean up tenant after test
     */
    protected function tearDownTenant(): void
    {
        BaseTenant::forgetCurrent();
    }
}
