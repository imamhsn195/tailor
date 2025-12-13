<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShurjopayPaymentService implements PaymentGatewayInterface
{
    protected array $credentials;
    protected string $baseUrl;

    public function __construct(array $credentials = [])
    {
        $this->credentials = $credentials;
        $this->baseUrl = $credentials['sandbox'] ?? false
            ? 'https://sandbox.shurjopayment.com'
            : 'https://shurjopayment.com';
    }

    /**
     * Create a payment/subscription
     */
    public function createPayment(array $data): array
    {
        try {
            $username = $this->credentials['username'] ?? '';
            $password = $this->credentials['password'] ?? '';
            $prefix = $this->credentials['prefix'] ?? '';

            $postData = [
                'username' => $username,
                'password' => $password,
                'prefix' => $prefix,
                'amount' => $data['amount'],
                'order_id' => 'TXN' . time() . rand(1000, 9999),
                'currency' => $data['currency'] ?? 'BDT',
                'customer_name' => $data['customer_name'] ?? '',
                'customer_phone' => $data['customer_phone'] ?? '',
                'customer_email' => $data['customer_email'] ?? '',
                'customer_address' => $data['customer_address'] ?? '',
                'customer_city' => $data['customer_city'] ?? 'Dhaka',
                'customer_postcode' => $data['customer_postcode'] ?? '1200',
                'return_url' => route('payment.shurjopay.success'),
                'cancel_url' => route('payment.shurjopay.cancel'),
            ];

            $response = Http::asJson()->post($this->baseUrl . '/api/get_token', $postData);

            if ($response->successful() && isset($response['checkout_url'])) {
                return [
                    'status' => 'success',
                    'transaction_id' => $postData['order_id'],
                    'redirect_url' => $response['checkout_url'],
                    'token' => $response['token'] ?? null,
                ];
            }

            return [
                'status' => 'error',
                'message' => $response['message'] ?? 'Payment initiation failed',
            ];
        } catch (\Exception $e) {
            Log::error("ShurjoPay payment error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $username = $this->credentials['username'] ?? '';
            $password = $this->credentials['password'] ?? '';

            $response = Http::asJson()->post($this->baseUrl . '/api/verification', [
                'username' => $username,
                'password' => $password,
                'order_id' => $transactionId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'verified' => isset($data['status']) && $data['status'] === 'Success',
                    'data' => $data,
                ];
            }

            return [
                'status' => 'error',
                'verified' => false,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'verified' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        return false;
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): array
    {
        try {
            return [
                'status' => 'success',
                'transaction_id' => $payload['order_id'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'status' => $payload['status'] ?? null,
                'data' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error("ShurjoPay webhook error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods(): array
    {
        return ['bkash', 'rocket', 'nagad', 'card', 'bank'];
    }
}

