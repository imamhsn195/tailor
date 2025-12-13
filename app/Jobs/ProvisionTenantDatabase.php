<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Multitenancy\Jobs\TenantAware;

class ProvisionTenantDatabase implements ShouldQueue, TenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Tenant $tenant,
        public array $adminData = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TenantProvisioningService $service): void
    {
        $service->provision($this->tenant, $this->adminData);
    }
}
