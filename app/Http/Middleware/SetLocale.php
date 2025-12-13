<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale', 'en'));
        
        // Validate locale (only allow 'en' and 'bn')
        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }
        
        app()->setLocale($locale);
        
        return $next($request);
    }
}

