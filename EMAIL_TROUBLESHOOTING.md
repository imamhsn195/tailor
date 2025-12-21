# Email Troubleshooting Guide

## Issue: Tenant Welcome Email Not Being Sent

### What Was Fixed

1. **Success Callback Provisioning**: Added tenant provisioning in the payment success callback as a fallback (in case webhook doesn't fire)
2. **Email Metadata Storage**: Customer email is now stored in subscription metadata for later use
3. **User Password Handling**: Fixed issue where existing users didn't have a password to send in email

### How It Works Now

1. **Payment Success**: When payment succeeds, the success callback:
   - Activates the tenant
   - Dispatches `ProvisionTenantDatabase` job with email sending enabled
   - Stores customer email in subscription metadata

2. **Webhook Processing**: When webhook is received:
   - Processes payment
   - Dispatches `ProvisionTenantDatabase` job with email sending enabled

3. **Provisioning Job**: The job:
   - Creates tenant database
   - Runs migrations
   - Seeds default data
   - Creates admin user
   - Sends welcome email with credentials

### Queue Configuration

The email is sent via a queued job. You need to ensure the queue is being processed:

#### Option 1: Use Sync Queue (Recommended for Development)

In your `.env` file:
```env
QUEUE_CONNECTION=sync
```

This will process jobs immediately without needing a queue worker.

#### Option 2: Run Queue Worker

If using `database` or `redis` queue:
```bash
php artisan queue:work
```

Or for development with auto-reload:
```bash
php artisan queue:listen
```

### Testing Email Sending

1. **Check Mail Configuration**: Ensure mail is configured in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tailor.test
MAIL_FROM_NAME="${APP_NAME}"
```

2. **Check Logs**: Look for email sending errors in:
   - `storage/logs/laravel.log`
   - `storage/logs/subscription-*.log`

3. **Check Email Logs Table**: If `email_logs` table exists, check for email records:
```sql
SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10;
```

### Common Issues

#### Issue: Email Not Sent After Payment

**Solution**: 
- Check if queue worker is running
- Set `QUEUE_CONNECTION=sync` in `.env` for immediate processing
- Check application logs for errors

#### Issue: Email Sent But Not Received

**Solution**:
- Check spam folder
- Verify email address is correct
- Check mail server configuration
- Use Mailtrap or similar service for testing

#### Issue: "Failed to send welcome email" in Logs

**Solution**:
- Check mail configuration
- Verify SMTP credentials
- Check if mail service is enabled: `MAIL_ENABLED=true` in config

### Manual Email Resend

If email wasn't sent, you can manually trigger it:

1. **Via Artisan Command** (create if needed):
```bash
php artisan tenant:send-welcome-email {tenant_id} --email=customer@example.com
```

2. **Via Database**:
- Find tenant ID
- Get customer email from subscription metadata
- Manually call provisioning service

### Verification Steps

1. ✅ Check tenant is `active` in database
2. ✅ Check subscription is `active`
3. ✅ Check `jobs` table for pending jobs
4. ✅ Check `email_logs` table for sent emails
5. ✅ Verify mail configuration
6. ✅ Check queue worker is running (if not using sync)

### Email Content

The welcome email includes:
- Tenant login URL
- Admin email address
- Temporary password
- Security instructions
- Getting started guide

### Next Steps

After fixing the issue:
1. Test with a new subscription
2. Monitor logs during provisioning
3. Verify email is received
4. Test login with provided credentials


