<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Multitenancy\Models\Tenant;

class EnsureTenantActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            // Redirect to subscriptions page to create a tenant
            return redirect()->route('subscriptions.index')
                ->with('info', 'Please create a subscription to access the admin area.');
        }
        
        if (!$tenant->isActive()) {
            abort(403, 'Tenant is not active');
        }
        
        return $next($request);
    }
}
