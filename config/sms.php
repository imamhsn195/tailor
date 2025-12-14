<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your SMS gateway settings here. Supported gateways:
    | - log: Log messages to Laravel log (for testing)
    | - sslcommerz: SSLCOMMERZ SMS API
    | - twilio: Twilio SMS API
    | - nexmo: Nexmo/Vonage SMS API
    |
    */

    'enabled' => env('SMS_ENABLED', false),

    'gateway' => env('SMS_GATEWAY', 'log'),

    'credentials' => [
        'sslcommerz' => [
            'api_key' => env('SMS_SSLCOMMERZ_API_KEY'),
            'sid' => env('SMS_SSLCOMMERZ_SID'),
            'url' => env('SMS_SSLCOMMERZ_URL', 'https://sms.sslwireless.com/api/v3/send-sms'),
        ],

        'twilio' => [
            'account_sid' => env('SMS_TWILIO_ACCOUNT_SID'),
            'auth_token' => env('SMS_TWILIO_AUTH_TOKEN'),
            'from' => env('SMS_TWILIO_FROM'),
        ],

        'nexmo' => [
            'api_key' => env('SMS_NEXMO_API_KEY'),
            'api_secret' => env('SMS_NEXMO_API_SECRET'),
            'from' => env('SMS_NEXMO_FROM'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    |
    | Define SMS message templates for different scenarios
    |
    */

    'templates' => [
        'order_created' => 'Dear {customer_name}, your order #{order_number} has been created. Total: {total_amount}. Delivery Date: {delivery_date}. Thank you!',
        'order_delivered' => 'Dear {customer_name}, your order #{order_number} has been delivered. Thank you for your business!',
        'delivery_reminder' => 'Dear {customer_name}, your order #{order_number} is scheduled for delivery on {delivery_date}. Please be available.',
        'payment_received' => 'Dear {customer_name}, payment of {amount} received for order #{order_number}. Thank you!',
    ],
];


