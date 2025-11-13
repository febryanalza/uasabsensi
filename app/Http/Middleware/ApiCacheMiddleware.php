<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiCacheMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Create cache key from request
        $cacheKey = 'api_cache_' . md5($request->getUri() . serialize($request->query()));

        // Try to get from cache
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse !== null) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->header('Content-Type', 'application/json')
                ->header('X-Cache-Status', 'HIT')
                ->header('Cache-Control', 'public, max-age=' . $ttl);
        }

        // Get fresh response
        $response = $next($request);

        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode()
            ], $ttl);
        }

        return $response->header('X-Cache-Status', 'MISS')
                       ->header('Cache-Control', 'public, max-age=' . $ttl);
    }
}