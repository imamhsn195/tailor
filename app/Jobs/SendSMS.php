<?php

namespace App\Jobs;

use App\Services\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Multitenancy\Jobs\TenantAware;

class SendSMS implements ShouldQueue, TenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $to;
    public string $message;
    public array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $message, array $options = [])
    {
        $this->to = $to;
        $this->message = $message;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(SMSService $smsService): void
    {
        $smsService->send($this->to, $this->message, $this->options);
    }
}


