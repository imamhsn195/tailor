# Tenant Login Guide

This guide explains how tenants can access and use the application after their account has been activated.

## Overview

When a tenant subscribes and pays for a plan, their account goes through the following process:

1. **Subscription Created**: A subscription record is created with status `pending`
2. **Payment Processed**: Payment webhook processes the payment
3. **Tenant Activated**: Tenant status changes from `pending` to `active`
4. **Database Provisioned**: A dedicated database is created for the tenant
5. **Admin User Created**: An admin user account is automatically created
6. **Welcome Email Sent**: Login credentials are sent to the tenant's email

## How Tenants Access Their Account

### 1. Domain/Subdomain Access

Tenants access their account using either:

- **Subdomain**: `{tenant-domain}.yourdomain.com`
- **Custom Domain**: If configured, tenants can use their own verified domain

The system automatically identifies the tenant based on the domain/subdomain used to access the application.

### 2. Login Process

#### Step 1: Access the Login Page

Tenants should navigate to their tenant-specific URL:
- **Subdomain**: `http://{tenant-domain}.yourdomain.com/login`
- **Custom Domain**: `http://{custom-domain}/login`

**Important**: Tenants MUST access via their tenant domain/subdomain. Accessing the main domain will not work for tenant login.

#### Step 2: Use Credentials from Welcome Email

After tenant activation, an automated welcome email is sent containing:

- **Login URL**: The tenant-specific login URL
- **Email Address**: The admin user's email address
- **Temporary Password**: A randomly generated password

#### Step 3: First Login

1. Enter the email address from the welcome email
2. Enter the temporary password
3. Click "Login"
4. **IMPORTANT**: Change the password immediately after first login

### 3. Login Credentials

#### Default Admin User

When a tenant is provisioned, a default admin user is created with:

- **Email**: Usually `admin@{tenant-domain}` or the email provided during subscription
- **Password**: Randomly generated 12-character password
- **Role**: Super Admin (full access)
- **Status**: Active

#### Credentials Location

The credentials are sent via email to the email address provided during subscription. If no email was provided, it defaults to `admin@{tenant-domain}`.

## Configuration

### Main Domain Configuration

Set your main domain in `.env`:

```env
APP_URL=http://yourdomain.com
```

Or configure it in `config/app.php`:

```php
'main_domain' => env('APP_MAIN_DOMAIN', 'yourdomain.com'),
```

### Local Development

For local development, you can use:

- `{tenant-domain}.localhost`
- `{tenant-domain}.127.0.0.1`
- `{tenant-domain}.test`

Make sure to add these to your `/etc/hosts` file (Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1 tenant1.localhost
127.0.0.1 tenant2.localhost
```

## Troubleshooting

### Tenant Cannot Login

1. **Check Tenant Status**: Ensure tenant status is `active` in the landlord database
2. **Check Subscription**: Ensure subscription status is `active`
3. **Verify Domain**: Ensure accessing via correct subdomain/domain
4. **Check User Status**: Ensure admin user is active in tenant database
5. **Check Email**: Verify welcome email was received (check spam folder)

### Email Not Received

If the tenant didn't receive the welcome email:

1. Check email logs in the database (`email_logs` table)
2. Verify mail configuration in `.env`
3. Check application logs for email sending errors
4. Manually send credentials using the super admin panel

### Domain Not Working

1. **Subdomain**: Ensure DNS is configured correctly
2. **Custom Domain**: 
   - Domain must be added in super admin panel
   - Domain must be verified
   - DNS records must point to your server

## Manual Credential Reset

If a tenant loses their credentials, you can:

### Option 1: Reset via Super Admin Panel

1. Login as super admin
2. Navigate to Tenants
3. Find the tenant
4. Use the "Reset Password" feature (if available)

### Option 2: Reset via Database

1. Connect to the tenant's database
2. Find the admin user in the `users` table
3. Update the password:

```php
use Illuminate\Support\Facades\Hash;
$user->password = Hash::make('new_password');
$user->save();
```

### Option 3: Resend Welcome Email

You can manually trigger the welcome email by:

1. Using the `ProvisionTenant` command with email option
2. Or create a custom command to resend credentials

## Security Best Practices

1. **Change Default Password**: Always change the temporary password after first login
2. **Use Strong Passwords**: Enforce strong password policies
3. **Enable 2FA**: Consider implementing two-factor authentication
4. **Monitor Access**: Review login history regularly
5. **IP/MAC Restrictions**: Use IP/MAC blocking for additional security

## Post-Login Setup

After logging in, tenants should:

1. **Change Password**: Update the temporary password
2. **Configure Company**: Set up company information
3. **Create Branches**: Add branch locations if needed
4. **Add Users**: Create additional user accounts for team members
5. **Configure Settings**: Customize system settings
6. **Set Up Products**: Add products and inventory
7. **Configure Payment**: Set up payment gateways if needed

## Support

If tenants encounter issues:

1. Check this guide first
2. Review application logs
3. Contact system administrator
4. Check tenant status in super admin panel

## Technical Details

### Tenant Identification

The system uses `TenantFinder` class to identify tenants:

1. First checks custom domains (verified)
2. Then checks subdomain matching
3. Returns `null` if no tenant found

### Authentication Flow

1. User accesses tenant domain
2. `IdentifyTenant` middleware identifies tenant
3. Tenant database is switched automatically
4. User authenticates against tenant database
5. Session is created in tenant context

### Database Isolation

Each tenant has:
- Separate database
- Isolated data
- Independent user accounts
- Separate configurations

## Related Files

- `app/TenantFinder.php` - Tenant identification logic
- `app/Http/Middleware/IdentifyTenant.php` - Tenant middleware
- `app/Services/TenantProvisioningService.php` - Tenant provisioning
- `app/Mail/TenantWelcomeEmail.php` - Welcome email
- `app/Http/Controllers/Auth/LoginController.php` - Login handling

