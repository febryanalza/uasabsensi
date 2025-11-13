<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PerformanceOptimization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enable query cache for read operations
        if ($request->isMethod('GET')) {
            DB::enableQueryLog();
        }

        // Add response caching headers for static content
        $response = $next($request);

        // Add performance headers
        if ($request->isMethod('GET') && !$request->is('api/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=300'); // 5 minutes cache
        }

        // Add compression headers
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }
}