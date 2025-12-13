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
        
        // Extract subdomain from host (e.g., tenant1.example.com -> tenant1)
        $subdomain = $this->extractSubdomain($host);
        
        if (!$subdomain) {
            return null;
        }
        
        // Find tenant by domain (subdomain)
        $tenant = Tenant::where('domain', $subdomain)
            ->where('status', 'active')
            ->first();
        
        // Also check custom domains
        if (!$tenant) {
            $tenant = Tenant::whereHas('domains', function ($query) use ($host) {
                $query->where('domain', $host)
                    ->where('is_verified', true);
            })
            ->where('status', 'active')
            ->first();
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
