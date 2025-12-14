<?php

namespace App\Jobs;

use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Multitenancy\Jobs\TenantAware;

class SendEmail implements ShouldQueue, TenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to;
    public string $subject;
    public string $view;
    public array $data;
    public array $options;

    /**
     * Create a new job instance.
     */
    public function __construct($to, string $subject, string $view, array $data = [], array $options = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        $emailService->send($this->to, $this->subject, $this->view, $this->data, $this->options);
    }
}


