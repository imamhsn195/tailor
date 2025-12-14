<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable activity logging during tests for better performance
        config(['activitylog.enabled' => false]);
        
        // Run tenant migrations after base migrations
        $this->runTenantMigrations();
    }
    
    /**
     * Run tenant migrations for testing
     */
    protected function runTenantMigrations(): void
    {
        if (file_exists(database_path('migrations/tenant'))) {
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
            } catch (\Exception $e) {
                // Ignore errors if tables already exist (handled by migration checks)
            }
        }
    }
}
