<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslcommerzPaymentService implements PaymentGatewayInterface
{
    protected array $credentials;
    protected string $baseUrl;

    public function __construct(array $credentials = [])
    {
        $this->credentials = $credentials;
        $this->baseUrl = $credentials['sandbox'] ?? false
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    /**
     * Create a payment/subscription
     */
    public function createPayment(array $data): array
    {
        try {
            $storeId = $this->credentials['store_id'] ?? '';
            $storePassword = $this->credentials['store_password'] ?? '';

            $postData = [
                'store_id' => $storeId,
                'store_passwd' => $storePassword,
                'total_amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BDT',
                'tran_id' => 'TXN' . time() . rand(1000, 9999),
                'success_url' => route('payment.sslcommerz.success'),
                'fail_url' => route('payment.sslcommerz.fail'),
                'cancel_url' => route('payment.sslcommerz.cancel'),
                'ipn_url' => route('webhook.sslcommerz'),
                'cus_name' => $data['customer_name'] ?? '',
                'cus_email' => $data['customer_email'] ?? '',
                'cus_phone' => $data['customer_phone'] ?? '',
                'cus_add1' => $data['customer_address'] ?? '',
                'cus_city' => $data['customer_city'] ?? 'Dhaka',
                'cus_country' => 'Bangladesh',
                'shipping_method' => 'NO',
                'product_name' => $data['product_name'] ?? 'Subscription',
                'product_category' => 'Subscription',
                'product_profile' => 'non-physical-goods',
            ];

            $response = Http::asForm()->post($this->baseUrl . '/gwprocess/v4/api.php', $postData);

            if ($response->successful() && isset($response['status']) && $response['status'] === 'SUCCESS') {
                return [
                    'status' => 'success',
                    'transaction_id' => $postData['tran_id'],
                    'redirect_url' => $response['GatewayPageURL'] ?? null,
                    'sessionkey' => $response['sessionkey'] ?? null,
                ];
            }

            return [
                'status' => 'error',
                'message' => $response['failedreason'] ?? 'Payment initiation failed',
            ];
        } catch (\Exception $e) {
            Log::error("SSLCOMMERZ payment error: " . $e->getMessage());
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
            $storeId = $this->credentials['store_id'] ?? '';
            $storePassword = $this->credentials['store_password'] ?? '';

            $response = Http::asForm()->post($this->baseUrl . '/validator/api/validationserverAPI.php', [
                'store_id' => $storeId,
                'store_passwd' => $storePassword,
                'val_id' => $transactionId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'verified' => isset($data['status']) && $data['status'] === 'VALID',
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
        // SSLCOMMERZ doesn't support subscription cancellation via API
        // This would need to be handled manually
        return false;
    }

    /**
     * Handle webhook/IPN
     */
    public function handleWebhook(array $payload): array
    {
        try {
            $storeId = $this->credentials['store_id'] ?? '';
            $storePassword = $this->credentials['store_password'] ?? '';

            // Verify IPN
            $valId = $payload['val_id'] ?? null;
            if (!$valId) {
                return ['status' => 'error', 'message' => 'Invalid IPN data'];
            }

            $response = Http::asForm()->post($this->baseUrl . '/validator/api/validationserverAPI.php', [
                'store_id' => $storeId,
                'store_passwd' => $storePassword,
                'val_id' => $valId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'transaction_id' => $data['tran_id'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'status' => $data['status'] ?? null,
                    'data' => $data,
                ];
            }

            return ['status' => 'error', 'message' => 'IPN verification failed'];
        } catch (\Exception $e) {
            Log::error("SSLCOMMERZ webhook error: " . $e->getMessage());
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

