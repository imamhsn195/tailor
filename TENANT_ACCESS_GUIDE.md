# Tenant Access Guide - Quick Reference

## Your Setup

- **Main Domain**: `http://tailor.test/`
- **Tenant Domain**: `asiafashoin222.test`

## How Your Tenant Will Access the System

### Step 1: Access Tenant URL

Your tenant should visit:
```
http://asiafashoin222.test/login
```

**Important**: They must use `asiafashoin222.test`, NOT `tailor.test`. Each tenant has their own subdomain/domain.

### Step 2: Login Credentials

After the tenant is activated, they will receive a welcome email with:
- **Login URL**: `http://asiafashoin222.test/login`
- **Email**: Usually `admin@asiafashoin222.test` or the email provided during subscription
- **Password**: A randomly generated temporary password

### Step 3: First Login

1. Go to `http://asiafashoin222.test/login`
2. Enter the email from the welcome email
3. Enter the temporary password
4. Click "Login"
5. **Change the password immediately** after first login

## Local Development Setup

### For Windows (XAMPP)

1. **Edit hosts file**: `C:\Windows\System32\drivers\etc\hosts`
2. Add this line:
   ```
   127.0.0.1    asiafashoin222.test
   ```
3. **Configure Virtual Host** in `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName asiafashoin222.test
       DocumentRoot "C:/xampp/htdocs/tailor/public"
       <Directory "C:/xampp/htdocs/tailor/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
4. **Restart Apache**

### Testing

1. Open browser
2. Visit: `http://asiafashoin222.test`
3. You should see the tenant's login page

## URL Structure

- **Main App**: `http://tailor.test/` (for subscriptions, super admin)
- **Tenant 1**: `http://asiafashoin222.test/` (tenant-specific)
- **Tenant 2**: `http://{another-tenant-domain}.test/` (if you have more tenants)

## Troubleshooting

### Tenant Cannot Access

1. **Check hosts file**: Ensure `asiafashoin222.test` points to `127.0.0.1`
2. **Check virtual host**: Ensure Apache virtual host is configured
3. **Check tenant status**: Ensure tenant is `active` in database
4. **Check subscription**: Ensure subscription is `active`

### 404 Error

- Verify virtual host configuration
- Check Apache is running
- Verify DocumentRoot points to `public` folder
- Check `.htaccess` file exists in `public` folder

### Wrong Tenant Loaded

- Clear browser cache
- Check tenant domain in database matches URL
- Verify `TenantFinder` is working correctly

## Database Check

To verify tenant is set up correctly:

```sql
-- In landlord database
SELECT id, name, domain, status FROM tenants WHERE domain = 'asiafashoin222.test';
```

Should return:
- `id`: Tenant ID
- `name`: Tenant name
- `domain`: `asiafashoin222.test`
- `status`: `active`

## Email Check

If tenant didn't receive welcome email:

1. Check `email_logs` table in landlord database
2. Verify mail configuration in `.env`
3. Check application logs: `storage/logs/laravel.log`

## Quick Test

1. Visit: `http://asiafashoin222.test/login`
2. If you see login page → Tenant is identified correctly ✅
3. If you see main app → Tenant not found ❌

## Next Steps After Login

Once logged in, tenant should:
1. Change password
2. Configure company settings
3. Add branches
4. Create additional users
5. Set up products and inventory

