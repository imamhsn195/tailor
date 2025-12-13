<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Multitenancy\Models\Tenant;

class EnsureSubscriptionActive
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
            abort(404, 'Tenant not found');
        }
        
        // Check if tenant has active subscription
        $subscription = $tenant->activeSubscription;
        
        if (!$subscription || !$subscription->isActive()) {
            // Allow access to subscription/billing pages
            if ($request->routeIs('subscription.*', 'billing.*', 'onboarding.*')) {
                return $next($request);
            }
            
            abort(403, 'Subscription is not active');
        }
        
        return $next($request);
    }
}
