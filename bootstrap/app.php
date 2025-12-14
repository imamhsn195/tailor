<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tenant identification middleware (runs early to identify tenant)
        $middleware->web(prepend: [
            \App\Http\Middleware\IdentifyTenant::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        // Register middleware aliases
        $middleware->alias([
            'identifytenant' => \App\Http\Middleware\IdentifyTenant::class,
            'ensuretenantactive' => \App\Http\Middleware\EnsureTenantActive::class,
            'ensuresubscriptionactive' => \App\Http\Middleware\EnsureSubscriptionActive::class,
        ]);
        
        // Exclude webhook routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
