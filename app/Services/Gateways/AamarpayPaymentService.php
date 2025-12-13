<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AamarpayPaymentService implements PaymentGatewayInterface
{
    protected array $credentials;
    protected string $baseUrl;

    public function __construct(array $credentials = [])
    {
        $this->credentials = $credentials;
        $this->baseUrl = $credentials['sandbox'] ?? false
            ? 'https://sandbox.aamarpay.com'
            : 'https://secure.aamarpay.com';
    }

    /**
     * Create a payment/subscription
     */
    public function createPayment(array $data): array
    {
        try {
            $storeId = $this->credentials['store_id'] ?? '';
            $signatureKey = $this->credentials['signature_key'] ?? '';

            $postData = [
                'store_id' => $storeId,
                'signature_key' => $signatureKey,
                'tran_id' => 'TXN' . time() . rand(1000, 9999),
                'success_url' => route('payment.aamarpay.success'),
                'fail_url' => route('payment.aamarpay.fail'),
                'cancel_url' => route('payment.aamarpay.cancel'),
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BDT',
                'desc' => $data['product_name'] ?? 'Subscription',
                'cus_name' => $data['customer_name'] ?? '',
                'cus_email' => $data['customer_email'] ?? '',
                'cus_phone' => $data['customer_phone'] ?? '',
                'cus_add1' => $data['customer_address'] ?? '',
                'cus_city' => $data['customer_city'] ?? 'Dhaka',
                'cus_country' => 'Bangladesh',
            ];

            $response = Http::asForm()->post($this->baseUrl . '/request.php', $postData);

            if ($response->successful() && isset($response['payment_url'])) {
                return [
                    'status' => 'success',
                    'transaction_id' => $postData['tran_id'],
                    'redirect_url' => $response['payment_url'],
                ];
            }

            return [
                'status' => 'error',
                'message' => $response['errorMessage'] ?? 'Payment initiation failed',
            ];
        } catch (\Exception $e) {
            Log::error("AamarPay payment error: " . $e->getMessage());
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
            $signatureKey = $this->credentials['signature_key'] ?? '';

            $response = Http::asForm()->post($this->baseUrl . '/api/v1/trxcheck/request.php', [
                'store_id' => $storeId,
                'signature_key' => $signatureKey,
                'request_id' => $transactionId,
                'type' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'verified' => isset($data['pay_status']) && $data['pay_status'] === 'Successful',
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
     * Handle webhook/callback
     */
    public function handleWebhook(array $payload): array
    {
        try {
            // Verify callback signature
            $signatureKey = $this->credentials['signature_key'] ?? '';
            $receivedSignature = $payload['signature'] ?? '';

            // Verify signature logic here
            // ...

            return [
                'status' => 'success',
                'transaction_id' => $payload['mer_txnid'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'status' => $payload['pay_status'] ?? null,
                'data' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error("AamarPay webhook error: " . $e->getMessage());
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

