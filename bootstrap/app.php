<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configure CSRF middleware for better session handling
        $middleware->validateCsrfTokens(except: [
            // No exceptions for now, but this ensures proper CSRF handling
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
