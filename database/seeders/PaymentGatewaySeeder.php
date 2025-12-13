<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'stripe',
                'display_name' => 'Stripe',
                'type' => 'international',
                'credentials' => [
                    'secret_key' => env('STRIPE_SECRET', ''),
                    'public_key' => env('STRIPE_KEY', ''),
                    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
                ],
                'supported_methods' => ['card'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'paddle',
                'display_name' => 'Paddle',
                'type' => 'international',
                'credentials' => [
                    'vendor_id' => env('PADDLE_VENDOR_ID', ''),
                    'api_key' => env('PADDLE_API_KEY', ''),
                    'sandbox' => env('PADDLE_SANDBOX', false),
                ],
                'supported_methods' => ['card', 'paypal'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'sslcommerz',
                'display_name' => 'SSLCOMMERZ',
                'type' => 'local',
                'credentials' => [
                    'store_id' => env('SSLCOMMERZ_STORE_ID', ''),
                    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', ''),
                    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
                ],
                'supported_methods' => ['bkash', 'rocket', 'nagad', 'card', 'bank'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'aamarpay',
                'display_name' => 'AamarPay',
                'type' => 'local',
                'credentials' => [
                    'store_id' => env('AAMARPAY_STORE_ID', ''),
                    'signature_key' => env('AAMARPAY_SIGNATURE_KEY', ''),
                    'sandbox' => env('AAMARPAY_SANDBOX', true),
                ],
                'supported_methods' => ['bkash', 'rocket', 'nagad', 'card', 'bank'],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'shurjopay',
                'display_name' => 'ShurjoPay',
                'type' => 'local',
                'credentials' => [
                    'username' => env('SHURJOPAY_USERNAME', ''),
                    'password' => env('SHURJOPAY_PASSWORD', ''),
                    'prefix' => env('SHURJOPAY_PREFIX', ''),
                    'sandbox' => env('SHURJOPAY_SANDBOX', true),
                ],
                'supported_methods' => ['bkash', 'rocket', 'nagad', 'card', 'bank'],
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['name' => $gateway['name']],
                $gateway
            );
        }
    }
}
