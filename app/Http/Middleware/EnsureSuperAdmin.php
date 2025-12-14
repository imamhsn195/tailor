<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has super admin access
        // You can implement your own logic here, e.g., check for a specific role or email
        $user = auth()->user();
        
        // Example: Check if user email is in super admin list
        $superAdminEmails = config('app.super_admin_emails', []);
        
        if (!in_array($user->email, $superAdminEmails)) {
            abort(403, 'Unauthorized. Super admin access required.');
        }

        return $next($request);
    }
}

