<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class TenantProvisioningService
{
    /**
     * Provision a new tenant
     *
     * @param Tenant $tenant
     * @param array $adminData
     * @return void
     * @throws Exception
     */
    public function provision(Tenant $tenant, array $adminData = []): void
    {
        // Ensure we're using landlord connection
        $tenant->setConnection('landlord');
        
        // Create database
        $this->createDatabase($tenant);
        
        // Run migrations
        $this->runMigrations($tenant);
        
        // Seed default data
        $this->seedDefaultData($tenant, $adminData);
        
        // Update tenant status
        $tenant->update(['status' => 'active']);
    }

    /**
     * Create tenant database
     *
     * @param Tenant $tenant
     * @return void
     * @throws Exception
     */
    protected function createDatabase(Tenant $tenant): void
    {
        $databaseName = $tenant->database_name;
        
        // Get database connection config
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $username = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', '');
        
        try {
            // Connect to MySQL without selecting a database
            $connection = new \PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Create database
            $charset = config('database.connections.mysql.charset', 'utf8mb4');
            $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
            
            $connection->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
            
        } catch (\PDOException $e) {
            throw new Exception("Failed to create database: " . $e->getMessage());
        }
    }

    /**
     * Run migrations on tenant database
     *
     * @param Tenant $tenant
     * @return void
     */
    protected function runMigrations(Tenant $tenant): void
    {
        $tenant->makeCurrent();
        
        try {
            // Run tenant migrations
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            
            // Publish Spatie Permission migrations if not already published
            $permissionMigrationsPath = database_path('migrations');
            if (!file_exists($permissionMigrationsPath . '/2018_01_01_000000_create_permission_tables.php')) {
                Artisan::call('vendor:publish', [
                    '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                    '--tag' => 'permission-migrations',
                ]);
            }
            
            // Run Spatie Permission migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);
            
            // Publish Spatie Activity Log migrations if not already published
            if (!file_exists($permissionMigrationsPath . '/2020_01_01_000000_create_activity_log_table.php')) {
                Artisan::call('vendor:publish', [
                    '--provider' => 'Spatie\Activitylog\ActivitylogServiceProvider',
                    '--tag' => 'activitylog-migrations',
                ]);
            }
            
            // Run Activity Log migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);
            
        } finally {
            Tenant::forgetCurrent();
        }
    }

    /**
     * Seed default data for tenant
     *
     * @param Tenant $tenant
     * @param array $adminData
     * @return void
     */
    protected function seedDefaultData(Tenant $tenant, array $adminData = []): void
    {
        $tenant->makeCurrent();
        
        try {
            // Seed all default data using TenantDatabaseSeeder
            Artisan::call('db:seed', [
                '--class' => 'TenantDatabaseSeeder',
                '--database' => 'tenant',
            ]);
            
            // Create admin user
            $this->createAdminUser($adminData);
            
        } finally {
            Tenant::forgetCurrent();
        }
    }

    /**
     * Create default roles and permissions
     * @deprecated Use RolesAndPermissionsSeeder instead
     * @return void
     */
    protected function createDefaultRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Company & Branch
            'company.view', 'company.edit',
            'branch.view', 'branch.create', 'branch.edit', 'branch.delete',
            
            // Users & Roles
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            
            // Customers
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            
            // Products
            'product.view', 'product.create', 'product.edit', 'product.delete',
            
            // Inventory
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            
            // Orders
            'order.view', 'order.create', 'order.edit', 'order.delete',
            
            // POS
            'pos.view', 'pos.create', 'pos.edit', 'pos.delete',
            
            // Factory
            'factory.view', 'factory.create', 'factory.edit', 'factory.delete',
            
            // HR & Payroll
            'hr.view', 'hr.create', 'hr.edit', 'hr.delete',
            
            // Accounting
            'accounting.view', 'accounting.create', 'accounting.edit', 'accounting.delete',
            
            // Reports
            'report.view', 'report.export',
            
            // Settings
            'settings.view', 'settings.edit',
            
            // System Logs
            'system-log.view', 'system-log.delete',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());
        
        // Create Admin role with most permissions (except user/role management)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereNotIn('name', [
            'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
        ])->get();
        $admin->givePermissionTo($adminPermissions);
        
        // Create Manager role
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'customer.view', 'customer.create', 'customer.edit',
            'product.view', 'product.create', 'product.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'order.view', 'order.create', 'order.edit',
            'pos.view', 'pos.create', 'pos.edit',
            'report.view', 'report.export',
        ])->get();
        $manager->givePermissionTo($managerPermissions);
        
        // Create Cashier role
        $cashier = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        $cashierPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'customer.view', 'customer.create',
            'pos.view', 'pos.create',
            'report.view',
        ])->get();
        $cashier->givePermissionTo($cashierPermissions);
    }

    /**
     * Create admin user for tenant
     *
     * @param array $adminData
     * @return void
     */
    protected function createAdminUser(array $adminData = []): void
    {
        $email = $adminData['email'] ?? 'admin@example.com';
        $name = $adminData['name'] ?? 'Admin';
        $password = $adminData['password'] ?? Str::random(12);
        
        // Check if user already exists
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }
        
        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole && !$user->hasRole($superAdminRole)) {
            $user->assignRole($superAdminRole);
        }
        
        // Store password for email (in real scenario, send email)
        if (isset($adminData['send_email']) && $adminData['send_email']) {
            // TODO: Send welcome email with credentials
        }
    }

    /**
     * Generate unique database name for tenant
     *
     * @param string $domain
     * @return string
     */
    public function generateDatabaseName(string $domain): string
    {
        $baseName = 'tenant_' . Str::slug($domain, '_');
        $randomSuffix = Str::random(8);
        
        return strtolower($baseName . '_' . $randomSuffix);
    }
}

