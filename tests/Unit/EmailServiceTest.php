<?php

namespace Tests\Unit;

use App\Services\EmailService;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant;
use Tests\TestCase;
use Mockery;

class EmailServiceTest extends TestCase
{
    protected EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailService = new EmailService();
    }

    /**
     * Test that email service returns disabled status when mail is disabled.
     */
    public function test_email_service_disabled(): void
    {
        config(['mail.enabled' => false]);
        $service = new EmailService();

        $result = $service->send('test@example.com', 'Test Subject', 'emails.test', []);

        $this->assertEquals('disabled', $result['status']);
        $this->assertEquals('Email service is disabled', $result['message']);
    }

    /**
     * Test that email service can send email successfully.
     */
    public function test_email_service_sends_email(): void
    {
        config(['mail.enabled' => true]);
        Mail::fake();
        
        $service = new EmailService();
        $result = $service->send('test@example.com', 'Test Subject', 'emails.test', []);

        Mail::assertSent(function ($mail) {
            return true;
        });

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('recipients', $result);
    }

    /**
     * Test that email service can send to multiple recipients.
     */
    public function test_email_service_sends_to_multiple_recipients(): void
    {
        config(['mail.enabled' => true]);
        Mail::fake();
        
        $service = new EmailService();
        $recipients = ['test1@example.com', 'test2@example.com'];
        $result = $service->send($recipients, 'Test Subject', 'emails.test', []);

        $this->assertEquals('success', $result['status']);
        $this->assertCount(2, $result['recipients']);
    }

    /**
     * Test that email service logs email when sent.
     */
    public function test_email_service_logs_email(): void
    {
        config(['mail.enabled' => true]);
        Mail::fake();
        
        $service = new EmailService();
        $service->send('test@example.com', 'Test Subject', 'emails.test', []);

        $this->assertDatabaseHas('email_logs', [
            'to' => 'test@example.com',
            'subject' => 'Test Subject',
            'status' => 'sent',
        ]);
    }

    /**
     * Test that email service handles exceptions gracefully.
     */
    public function test_email_service_handles_exceptions(): void
    {
        config(['mail.enabled' => true]);
        Mail::shouldReceive('send')->andThrow(new \Exception('Mail error'));
        
        $service = new EmailService();
        $result = $service->send('test@example.com', 'Test Subject', 'emails.test', []);

        $this->assertEquals('error', $result['status']);
        $this->assertArrayHasKey('message', $result);
        
        $this->assertDatabaseHas('email_logs', [
            'to' => 'test@example.com',
            'status' => 'failed',
        ]);
    }

    /**
     * Test that email service can send mailable.
     */
    public function test_email_service_sends_mailable(): void
    {
        config(['mail.enabled' => true]);
        Mail::fake();
        
        $order = \App\Models\Order::factory()->create();
        $mailable = new \App\Mail\OrderCreatedMail($order);
        $service = new EmailService();
        $result = $service->sendMailable('test@example.com', $mailable);

        Mail::assertSent(\App\Mail\OrderCreatedMail::class);
        $this->assertEquals('success', $result['status']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
