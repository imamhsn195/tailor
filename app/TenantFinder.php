<?php

namespace App;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant as TenantModel;
use Spatie\Multitenancy\TenantFinder\TenantFinder as BaseTenantFinder;

class TenantFinder extends BaseTenantFinder
{
    public function findForRequest(Request $request): ?TenantModel
    {
        $host = $request->getHost();
        
        // First, check custom domains (verified custom domains take priority)
        $tenant = Tenant::whereHas('domains', function ($query) use ($host) {
                $query->where('domain', $host)
                    ->where('is_verified', true);
            })
            ->where('status', 'active')
            ->first();
        
        // If no custom domain found, check if host matches tenant domain directly
        // (for cases where tenant domain is stored as full domain like "asiafashoin222.test")
        if (!$tenant) {
            $tenant = Tenant::where('domain', $host)
                ->where('status', 'active')
                ->first();
        }
        
        // If still not found, try subdomain extraction
        if (!$tenant) {
            $subdomain = $this->extractSubdomain($host);
            
            if ($subdomain) {
                $tenant = Tenant::where('domain', $subdomain)
                    ->where('status', 'active')
                    ->first();
            }
        }
        
        // Ensure tenant has database_name set before returning
        // This is critical for Spatie Multitenancy to properly configure the connection
        if ($tenant && empty($tenant->database_name)) {
            \Log::error('Tenant found but database_name is empty', [
                'tenant_id' => $tenant->id,
                'domain' => $tenant->domain,
            ]);
            return null;
        }
        
        return $tenant;
    }
    
    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Get main domain from config (e.g., example.com)
        $mainDomain = config('app.main_domain', parse_url(config('app.url'), PHP_URL_HOST));
        
        // Remove www. if present
        $host = preg_replace('/^www\./', '', $host);
        $mainDomain = preg_replace('/^www\./', '', $mainDomain);
        
        // If host is exactly the main domain, no subdomain
        if ($host === $mainDomain) {
            return null;
        }
        
        // Extract subdomain (everything before the main domain)
        if (str_ends_with($host, '.' . $mainDomain)) {
            return str_replace('.' . $mainDomain, '', $host);
        }
        
        // For local development (e.g., tenant1.localhost)
        if (str_contains($host, '.localhost') || str_contains($host, '.test')) {
            $parts = explode('.', $host);
            return $parts[0] !== 'www' ? $parts[0] : null;
        }
        
        return null;
    }
}
