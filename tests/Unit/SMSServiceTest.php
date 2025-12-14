<?php

namespace Tests\Unit;

use App\Services\SMSService;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SMSServiceTest extends TestCase
{
    /**
     * Test that SMS service returns disabled status when SMS is disabled.
     */
    public function test_sms_service_disabled(): void
    {
        config(['sms.enabled' => false]);
        $service = new SMSService();

        $result = $service->send('1234567890', 'Test message');

        $this->assertEquals('disabled', $result['status']);
        $this->assertEquals('SMS service is disabled', $result['message']);
    }

    /**
     * Test that SMS service sends via log gateway.
     */
    public function test_sms_service_sends_via_log(): void
    {
        config([
            'sms.enabled' => true,
            'sms.gateway' => 'log',
            'sms.credentials' => [],
        ]);
        
        Log::shouldReceive('info')->once();
        
        $service = new SMSService();
        $result = $service->send('1234567890', 'Test message');

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('message_id', $result);
    }

    /**
     * Test that SMS service logs SMS when sent.
     */
    public function test_sms_service_logs_sms(): void
    {
        config([
            'sms.enabled' => true,
            'sms.gateway' => 'log',
            'sms.credentials' => [],
        ]);
        
        Log::shouldReceive('info')->once();
        
        $service = new SMSService();
        $service->send('1234567890', 'Test message');

        $this->assertDatabaseHas('sms_logs', [
            'to' => '1234567890',
            'message' => 'Test message',
            'gateway' => 'log',
            'status' => 'success',
        ]);
    }

    /**
     * Test that SMS service sends via SSLCOMMERZ gateway.
     */
    public function test_sms_service_sends_via_sslcommerz(): void
    {
        config([
            'sms.enabled' => true,
            'sms.gateway' => 'sslcommerz',
            'sms.credentials' => [
                'api_key' => 'test_key',
                'sid' => 'test_sid',
                'url' => 'https://sms.sslwireless.com/api/v3/send-sms',
            ],
        ]);

        Http::fake([
            'sms.sslwireless.com/*' => Http::response([
                'status' => 'SUCCESS',
                'smsinfo' => [
                    ['smsid' => '12345']
                ]
            ], 200)
        ]);

        $service = new SMSService();
        $result = $service->send('1234567890', 'Test message');

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('message_id', $result);
    }

    /**
     * Test that SMS service handles exceptions gracefully.
     */
    public function test_sms_service_handles_exceptions(): void
    {
        config([
            'sms.enabled' => true,
            'sms.gateway' => 'log',
            'sms.credentials' => [],
        ]);
        
        Log::shouldReceive('info')->andThrow(new \Exception('SMS error'));
        Log::shouldReceive('error')->once();
        
        $service = new SMSService();
        $result = $service->send('1234567890', 'Test message');

        $this->assertEquals('error', $result['status']);
        $this->assertArrayHasKey('message', $result);
        
        $this->assertDatabaseHas('sms_logs', [
            'to' => '1234567890',
            'status' => 'failed',
        ]);
    }

    /**
     * Test that SMS service can send bulk SMS.
     */
    public function test_sms_service_sends_bulk(): void
    {
        config([
            'sms.enabled' => true,
            'sms.gateway' => 'log',
            'sms.credentials' => [],
        ]);
        
        Log::shouldReceive('info')->times(2);
        
        $service = new SMSService();
        $recipients = ['1234567890', '0987654321'];
        $result = $service->sendBulk($recipients, 'Test message');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('1234567890', $result);
        $this->assertArrayHasKey('0987654321', $result);
    }

    /**
     * Test that SMS service throws exception for unsupported gateway.
     */
    public function test_sms_service_throws_for_unsupported_gateway(): void
    {
        config([
            'sms.enabled' => true,
            'sms.gateway' => 'unsupported',
            'sms.credentials' => [],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported SMS gateway: unsupported');

        $service = new SMSService();
        $service->send('1234567890', 'Test message');
    }
}
