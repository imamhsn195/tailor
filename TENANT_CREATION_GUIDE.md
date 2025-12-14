# Tenant Creation Guide

This guide explains all the ways to create tenants in the Tailor Management System.

## Methods to Create Tenants

### 1. Through Super Admin Panel (Web Interface)

**Access:** `/super-admin/tenants/create`

**Requirements:**
- Must be logged in as a Super Admin user
- Super Admin middleware is required

**Steps:**
1. Navigate to Super Admin → Tenants
2. Click "Create Tenant" button
3. Fill in the form:
   - **Tenant Name**: Company/Organization name
   - **Domain**: Unique domain (e.g., `example.com`)
   - **Email**: Admin user email
   - **Password**: Admin user password (minimum 8 characters)
   - **Trial Days** (optional): Number of trial days
4. Submit the form

**What happens:**
- Tenant record is created in the main database
- Unique database name is generated
- Tenant database is created
- Migrations are run automatically
- Default seeders are run (roles, permissions, categories, etc.)
- Admin user is created with Super Admin role
- Tenant status is set to 'active'

**Route:** `POST /super-admin/tenants`

---

### 2. Through Subscription/Public Registration

**Access:** `/subscriptions` (Public route)

**Steps:**
1. Visit the subscriptions page
2. Select a subscription plan
3. Fill in the registration form:
   - **Tenant Name**: Your company name
   - **Domain**: Your desired domain
   - **Email**: Your email address
   - **Name**: Your full name
   - **Phone** (optional): Contact number
   - **Address** (optional): Your address
   - **Payment Gateway**: Choose payment method
4. Complete payment
5. Tenant is created after successful payment

**What happens:**
- Tenant is created with 'pending' status
- Payment is processed
- Subscription record is created
- After payment confirmation, tenant database is provisioned
- Admin user is created automatically

**Route:** `POST /subscriptions`

---

### 3. Through Artisan Command

**Command:** `php artisan tenant:provision`

**Usage:**
```bash
# Basic usage (interactive)
php artisan tenant:provision {tenant_id}

# With options
php artisan tenant:provision {tenant_id} \
    --email=admin@example.com \
    --name="Admin User" \
    --password=SecurePassword123
```

**Options:**
- `{tenant}`: Tenant ID or domain (must exist in database first)
- `--email`: Admin email (optional, will prompt if not provided)
- `--name`: Admin name (optional, will prompt if not provided)
- `--password`: Admin password (optional, will be auto-generated if not provided)

**Note:** This command provisions an **existing** tenant record. To create a new tenant record first, use method 4 or create it manually in the database.

**Example:**
```bash
# First, create tenant record (if not exists)
# Then provision it
php artisan tenant:provision 1 --email=admin@test.com --name="Admin" --password=password123
```

---

### 4. Programmatically (Code)

**Create Tenant Record:**
```php
use App\Models\Tenant;
use App\Services\TenantProvisioningService;

// Create tenant record
$tenant = Tenant::create([
    'name' => 'My Company',
    'domain' => 'mycompany.com',
    'database_name' => 'tenant_mycompany_' . Str::random(8),
    'status' => 'pending',
]);

// Provision tenant (creates database, runs migrations, seeds data)
$provisioningService = app(TenantProvisioningService::class);
$provisioningService->provision($tenant, [
    'email' => 'admin@mycompany.com',
    'name' => 'Admin User',
    'password' => 'SecurePassword123',
]);
```

**Or using the service's database name generator:**
```php
use App\Models\Tenant;
use App\Services\TenantProvisioningService;

$provisioningService = app(TenantProvisioningService::class);

$tenant = Tenant::create([
    'name' => 'My Company',
    'domain' => 'mycompany.com',
    'database_name' => $provisioningService->generateDatabaseName('mycompany.com'),
    'status' => 'pending',
]);

$provisioningService->provision($tenant, [
    'email' => 'admin@mycompany.com',
    'name' => 'Admin User',
    'password' => 'SecurePassword123',
]);
```

---

## Tenant Provisioning Process

When a tenant is provisioned, the following happens automatically:

1. **Database Creation**
   - Creates a new MySQL database with the specified name
   - Uses UTF8MB4 charset and collation

2. **Migrations**
   - Runs all tenant migrations from `database/migrations/tenant/`
   - Publishes and runs Spatie Permission migrations
   - Publishes and runs Spatie Activity Log migrations

3. **Data Seeding**
   - Runs `TenantDatabaseSeeder` which includes:
     - `RolesAndPermissionsSeeder` - Creates roles and permissions
     - `ProductCategorySeeder` - Creates product categories
     - `ProductUnitSeeder` - Creates product units
     - `DepartmentSeeder` - Creates departments
     - `DesignationSeeder` - Creates designations
     - `WorkerCategorySeeder` - Creates worker categories
     - `ChartOfAccountSeeder` - Creates chart of accounts

4. **Admin User Creation**
   - Creates admin user with provided credentials
   - Assigns "Super Admin" role
   - Sets user as active

5. **Status Update**
   - Sets tenant status to 'active'

---

## Tenant Status

Tenants can have the following statuses:

- **pending**: Tenant created but not yet provisioned
- **active**: Tenant is active and operational
- **suspended**: Tenant is temporarily suspended
- **inactive**: Tenant is inactive/deactivated

---

## Manual Database Seeding

If you need to re-seed a tenant database:

```bash
# Seed with default TenantDatabaseSeeder
php artisan tenants:seed {tenant_id}

# Or by domain
php artisan tenants:seed example.com

# Run specific seeder
php artisan tenants:seed {tenant_id} --class=ProductCategorySeeder
```

---

## Troubleshooting

### Tenant Creation Fails

1. **Check database permissions**: Ensure MySQL user has CREATE DATABASE permission
2. **Check domain uniqueness**: Domain must be unique across all tenants
3. **Check database name**: Database name must be unique and valid
4. **Check logs**: Check `storage/logs/laravel.log` for detailed error messages

### Tenant Provisioning Fails

1. **Check tenant exists**: Tenant record must exist before provisioning
2. **Check migrations**: Ensure all tenant migrations are in `database/migrations/tenant/`
3. **Check database connection**: Verify tenant database connection in `config/database.php`
4. **Check seeders**: Ensure all seeder classes exist and are properly namespaced

### Re-provisioning a Tenant

If you need to re-provision an existing tenant:

```bash
php artisan tenant:provision {tenant_id}
```

The command will ask for confirmation if the tenant is already active.

**Warning:** Re-provisioning will:
- Drop and recreate the database (all data will be lost!)
- Run all migrations again
- Re-seed all default data
- Recreate admin user

---

## Quick Start Example

**Create a tenant via Super Admin:**
1. Login as super admin
2. Go to `/super-admin/tenants`
3. Click "Create Tenant"
4. Fill form and submit

**Create a tenant via command (if tenant record exists):**
```bash
php artisan tenant:provision 1 \
    --email=admin@test.com \
    --name="Admin" \
    --password=password123
```

**Create tenant programmatically:**
```php
$tenant = Tenant::create([
    'name' => 'Test Company',
    'domain' => 'test.local',
    'database_name' => 'tenant_test_' . time(),
    'status' => 'pending',
]);

app(TenantProvisioningService::class)->provision($tenant, [
    'email' => 'admin@test.local',
    'name' => 'Admin',
    'password' => 'password123',
]);
```

---

## Related Commands

- `php artisan tenant:provision` - Provision a tenant
- `php artisan tenants:seed` - Seed tenant database
- `php artisan tenants:artisan` - Run artisan commands on tenant databases
