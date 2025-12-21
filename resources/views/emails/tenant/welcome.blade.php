<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .credentials-box {
            background-color: white;
            border: 2px solid #4F46E5;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 4px;
        }
        .credential-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 18px;
            color: #111827;
            font-family: 'Courier New', monospace;
        }
        .button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #4338CA;
        }
        .warning {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to {{ config('app.name') }}!</h1>
        <p>Your tenant account has been activated</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $tenant->name }} Administrator,</p>
        
        <p>Congratulations! Your tenant account <strong>{{ $tenant->name }}</strong> has been successfully activated and is ready to use.</p>
        
        <p>Your tenant database has been provisioned and your admin account has been created. You can now access your dashboard using the credentials below:</p>
        
        <div class="credentials-box">
            <div class="credential-item">
                <div class="credential-label">Login URL</div>
                <div class="credential-value">{{ $loginUrl }}</div>
            </div>
            <div class="credential-item">
                <div class="credential-label">Email Address</div>
                <div class="credential-value">{{ $email }}</div>
            </div>
            <div class="credential-item">
                <div class="credential-label">Temporary Password</div>
                <div class="credential-value">{{ $password }}</div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Login to Dashboard</a>
        </div>
        
        <div class="warning">
            <strong>⚠️ Important Security Notice:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Please change your password immediately after your first login</li>
                <li>Keep your login credentials secure and do not share them</li>
                <li>If you did not request this account, please contact support immediately</li>
            </ul>
        </div>
        
        <h3>Getting Started</h3>
        <p>After logging in, you can:</p>
        <ul>
            <li>Configure your company and branch settings</li>
            <li>Set up your products and inventory</li>
            <li>Create additional user accounts for your team</li>
            <li>Customize your system preferences</li>
        </ul>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>
        {{ config('app.name') }} Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>

