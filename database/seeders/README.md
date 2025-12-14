# Database Seeders

This directory contains seeders for initializing the application with default data.

## Main Database Seeders

These seeders are for the main database (not tenant databases):

### DatabaseSeeder
Main seeder that calls all main database seeders:
- `PaymentGatewaySeeder` - Payment gateway configurations
- `PlanSeeder` - Subscription plans

**Usage:**
```bash
php artisan db:seed
```

### PaymentGatewaySeeder
Seeds payment gateway configurations (Stripe, Paddle, SSLCommerz, AamarPay, ShurjoPay).

### PlanSeeder
Seeds subscription plans (Starter, Professional, Enterprise with monthly and annual options).

## Tenant Database Seeders

These seeders are for tenant databases (run after tenant creation):

### TenantDatabaseSeeder
Main tenant seeder that calls all tenant-specific seeders:
- `RolesAndPermissionsSeeder` - Roles and permissions
- `ProductCategorySeeder` - Product categories
- `ProductUnitSeeder` - Product units
- `DepartmentSeeder` - Departments
- `DesignationSeeder` - Designations
- `WorkerCategorySeeder` - Worker categories
- `ChartOfAccountSeeder` - Chart of accounts

**Usage for a tenant:**
```bash
# Seed with default TenantDatabaseSeeder
php artisan tenants:seed {tenant_id}

# Or specify tenant by domain
php artisan tenants:seed example.com

# Or run a specific seeder
php artisan tenants:seed {tenant_id} --class=ProductCategorySeeder
```

Or programmatically:
```php
$tenant->makeCurrent();
$this->call(TenantDatabaseSeeder::class);
Tenant::forgetCurrent();
```

### RolesAndPermissionsSeeder
Creates:
- All permissions for the application
- Roles: Super Admin, Admin, Manager, Cashier, Tailor, Accountant
- Assigns appropriate permissions to each role

### ProductCategorySeeder
Creates default product categories:
- Shirt, Pant, Sherwani, Kurta, Panjabi, Suit, Blazer, Waistcoat, Fabric, Accessories

### ProductUnitSeeder
Creates default product units:
- Piece (PCS), Meter (M), Yard (YD), Feet (FT), Kilogram (KG), Gram (G), Dozen (DZ), Set (SET)

### DepartmentSeeder
Creates default departments:
- Cutting, Sewing, Finishing, Embroidery, Design, Sales, Administration, Accounts, HR, Store

### DesignationSeeder
Creates default designations:
- Manager, Supervisor, Master Tailor, Tailor, Cutter, Helper, Sales Executive, Cashier, Accountant, Designer, Embroidery Worker, Quality Controller

### WorkerCategorySeeder
Creates default worker categories with daily wages:
- Master Tailor, Senior Tailor, Tailor, Junior Tailor, Cutter, Helper, Embroidery Worker, Finishing Worker

### ChartOfAccountSeeder
Creates default chart of accounts:
- Assets (Cash, Bank, Accounts Receivable, Inventory, Equipment)
- Liabilities (Accounts Payable, Loans)
- Equity (Capital, Retained Earnings)
- Revenue (Sales Revenue, Service Revenue)
- Expenses (COGS, Operating Expenses, Salary & Wages, Rent, Utilities, Marketing)

## Seeding Flow

### Initial Setup (Main Database)
1. Run migrations: `php artisan migrate`
2. Seed main database: `php artisan db:seed`

### Tenant Setup
1. Create tenant (via subscription or admin panel)
2. Tenant database is automatically created
3. Run tenant seeders: `php artisan tenants:seed --tenant={tenant_id}`

Or update `TenantProvisioningService` to automatically call `TenantDatabaseSeeder` when creating a tenant.

## Notes

- All seeders use `updateOrCreate` to prevent duplicates
- Seeders are idempotent - safe to run multiple times
- Tenant seeders should be run after tenant database is created
- Main database seeders should be run once during initial setup
