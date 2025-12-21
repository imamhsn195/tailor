<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Spatie Multitenancy automatically finds and makes tenant current
        // via the tenant_finder configured in config/multitenancy.php
        // This middleware is registered early to ensure tenant is identified
        
        // Ensure tenant connection is properly configured if tenant is current
        $tenant = \Spatie\Multitenancy\Models\Tenant::current();
        if ($tenant) {
            // Verify the tenant connection has a database name set
            $connection = \Illuminate\Support\Facades\DB::connection('tenant');
            $config = $connection->getConfig();
            
            if (empty($config['database'])) {
                // Connection doesn't have database name - this shouldn't happen
                // but if it does, clear the connection and let Spatie reconfigure it
                \Illuminate\Support\Facades\DB::purge('tenant');
                \Illuminate\Support\Facades\DB::disconnect('tenant');
                
                // Make tenant current again to force reconfiguration
                $tenant->makeCurrent();
            }
        }
        
        return $next($request);
    }
}
