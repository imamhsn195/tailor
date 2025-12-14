<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsLog;
use Spatie\Multitenancy\Models\Tenant;

class SMSService
{
    protected string $gateway;
    protected array $credentials;
    protected bool $enabled;

    public function __construct()
    {
        $this->gateway = config('sms.gateway', 'log');
        $this->credentials = config('sms.credentials', []);
        $this->enabled = config('sms.enabled', false);
    }

    /**
     * Send SMS message
     *
     * @param string $to Phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return array
     */
    public function send(string $to, string $message, array $options = []): array
    {
        if (!$this->enabled) {
            Log::info("SMS disabled. Would send to {$to}: {$message}");
            return ['status' => 'disabled', 'message' => 'SMS service is disabled'];
        }

        try {
            $tenant = Tenant::current();
            $tenantId = $tenant ? $tenant->id : null;
            $result = $this->sendViaGateway($to, $message, $options);

            // Log SMS
            try {
                SmsLog::create([
                    'tenant_id' => $tenantId,
                    'to' => $to,
                    'message' => $message,
                    'gateway' => $this->gateway,
                    'status' => $result['status'] ?? 'unknown',
                    'response' => $result['response'] ?? null,
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Table might not exist in testing environment
                Log::warning("Failed to log SMS: " . $e->getMessage());
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage());
            
            // Log failed SMS
            $tenant = Tenant::current();
            $tenantId = $tenant ? $tenant->id : null;
            try {
                SmsLog::create([
                    'tenant_id' => $tenantId,
                    'to' => $to,
                    'message' => $message,
                    'gateway' => $this->gateway,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
            } catch (\Exception $logException) {
                // Table might not exist in testing environment
                Log::warning("Failed to log SMS error: " . $logException->getMessage());
            }

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS via configured gateway
     */
    protected function sendViaGateway(string $to, string $message, array $options = []): array
    {
        return match($this->gateway) {
            'sslcommerz' => $this->sendViaSSLCOMMERZ($to, $message, $options),
            'twilio' => $this->sendViaTwilio($to, $message, $options),
            'nexmo' => $this->sendViaNexmo($to, $message, $options),
            'log' => $this->sendViaLog($to, $message, $options),
            default => throw new \InvalidArgumentException("Unsupported SMS gateway: {$this->gateway}"),
        };
    }

    /**
     * Send via SSLCOMMERZ SMS API
     */
    protected function sendViaSSLCOMMERZ(string $to, string $message, array $options = []): array
    {
        $apiKey = $this->credentials['api_key'] ?? '';
        $sid = $this->credentials['sid'] ?? '';
        $url = $this->credentials['url'] ?? 'https://sms.sslwireless.com/api/v3/send-sms';

        $response = Http::asForm()->post($url, [
            'api_token' => $apiKey,
            'sid' => $sid,
            'msisdn' => $to,
            'sms' => $message,
            'csms_id' => $options['csms_id'] ?? uniqid(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => $data['status'] === 'SUCCESS' ? 'success' : 'failed',
                'message_id' => $data['smsinfo'][0]['smsid'] ?? null,
                'response' => $data,
            ];
        }

        return [
            'status' => 'failed',
            'response' => $response->json(),
        ];
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $to, string $message, array $options = []): array
    {
        $accountSid = $this->credentials['account_sid'] ?? '';
        $authToken = $this->credentials['auth_token'] ?? '';
        $from = $this->credentials['from'] ?? '';

        $response = Http::asForm()->withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => 'success',
                'message_id' => $data['sid'] ?? null,
                'response' => $data,
            ];
        }

        return [
            'status' => 'failed',
            'response' => $response->json(),
        ];
    }

    /**
     * Send via Nexmo/Vonage
     */
    protected function sendViaNexmo(string $to, string $message, array $options = []): array
    {
        $apiKey = $this->credentials['api_key'] ?? '';
        $apiSecret = $this->credentials['api_secret'] ?? '';
        $from = $this->credentials['from'] ?? '';

        $response = Http::asForm()->post('https://rest.nexmo.com/sms/json', [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'from' => $from,
            'to' => $to,
            'text' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $messageStatus = $data['messages'][0]['status'] ?? '0';
            return [
                'status' => $messageStatus === '0' ? 'success' : 'failed',
                'message_id' => $data['messages'][0]['message-id'] ?? null,
                'response' => $data,
            ];
        }

        return [
            'status' => 'failed',
            'response' => $response->json(),
        ];
    }

    /**
     * Send via log (for testing/development)
     */
    protected function sendViaLog(string $to, string $message, array $options = []): array
    {
        Log::info("SMS [{$to}]: {$message}");
        return [
            'status' => 'success',
            'message_id' => 'log-' . uniqid(),
            'response' => ['logged' => true],
        ];
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        $results = [];
        foreach ($recipients as $to) {
            $results[$to] = $this->send($to, $message, $options);
        }
        return $results;
    }
}

