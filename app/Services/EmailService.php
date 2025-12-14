<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog;
use Spatie\Multitenancy\Models\Tenant;

class EmailService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('mail.enabled', true);
    }

    /**
     * Send email
     *
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $view Blade view name
     * @param array $data View data
     * @param array $options Additional options
     * @return array
     */
    public function send($to, string $subject, string $view, array $data = [], array $options = []): array
    {
        if (!$this->enabled) {
            Log::info("Email disabled. Would send to {$to}: {$subject}");
            return ['status' => 'disabled', 'message' => 'Email service is disabled'];
        }

        try {
            $tenant = Tenant::current();
            $tenantId = $tenant ? $tenant->id : null;
            $recipients = is_array($to) ? $to : [$to];

            foreach ($recipients as $recipient) {
                Mail::send($view, $data, function ($message) use ($recipient, $subject, $options) {
                    $message->to($recipient)
                        ->subject($subject);

                    if (isset($options['cc'])) {
                        $message->cc($options['cc']);
                    }

                    if (isset($options['bcc'])) {
                        $message->bcc($options['bcc']);
                    }

                    if (isset($options['attachments'])) {
                        foreach ($options['attachments'] as $attachment) {
                            $message->attach($attachment);
                        }
                    }
                });

                // Log email
                try {
                    EmailLog::create([
                        'tenant_id' => $tenantId,
                        'to' => $recipient,
                        'subject' => $subject,
                        'view' => $view,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Table might not exist in testing environment
                    Log::warning("Failed to log email: " . $e->getMessage());
                }
            }

            return [
                'status' => 'success',
                'recipients' => $recipients,
            ];
        } catch (\Exception $e) {
            Log::error("Email sending failed: " . $e->getMessage());

            // Log failed email
            $tenant = Tenant::current();
            $tenantId = $tenant ? $tenant->id : null;
            $recipients = is_array($to) ? $to : [$to];
            foreach ($recipients as $recipient) {
                try {
                    EmailLog::create([
                        'tenant_id' => $tenantId,
                        'to' => $recipient,
                        'subject' => $subject,
                        'view' => $view,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $logException) {
                    // Table might not exist in testing environment
                    Log::warning("Failed to log email error: " . $logException->getMessage());
                }
            }

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send email using Mailable class
     *
     * @param string|array $to Recipient email(s)
     * @param \Illuminate\Mail\Mailable $mailable Mailable instance
     * @return array
     */
    public function sendMailable($to, $mailable): array
    {
        if (!$this->enabled) {
            Log::info("Email disabled. Would send mailable to {$to}");
            return ['status' => 'disabled', 'message' => 'Email service is disabled'];
        }

        try {
            $tenant = Tenant::current();
            $tenantId = $tenant ? $tenant->id : null;
            $recipients = is_array($to) ? $to : [$to];

            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send($mailable);

                // Log email
                try {
                    EmailLog::create([
                        'tenant_id' => $tenantId,
                        'to' => $recipient,
                        'subject' => $mailable->subject ?? 'No Subject',
                        'view' => get_class($mailable),
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Table might not exist in testing environment
                    Log::warning("Failed to log email: " . $e->getMessage());
                }
            }

            return [
                'status' => 'success',
                'recipients' => $recipients,
            ];
        } catch (\Exception $e) {
            Log::error("Email sending failed: " . $e->getMessage());

            $tenant = Tenant::current();
            $tenantId = $tenant ? $tenant->id : null;
            $recipients = is_array($to) ? $to : [$to];
            foreach ($recipients as $recipient) {
                try {
                    EmailLog::create([
                        'tenant_id' => $tenantId,
                        'to' => $recipient,
                        'subject' => $mailable->subject ?? 'No Subject',
                        'view' => get_class($mailable),
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $logException) {
                    // Table might not exist in testing environment
                    Log::warning("Failed to log email error: " . $logException->getMessage());
                }
            }

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}


