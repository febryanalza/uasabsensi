<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startQueries = count(DB::getQueryLog());
        
        // Enable query logging if in debug mode
        if (config('debug.debug_errors.log_queries')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Log performance metrics
        if ($duration > config('debug.performance.response_time_limit')) {
            Log::warning('Slow response detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration' => round($duration, 2) . 'ms',
                'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
            ]);
        }

        if (config('debug.debug_errors.log_queries')) {
            $queries = DB::getQueryLog();
            $endQueries = count($queries);
            $queryCount = $endQueries - $startQueries;
            
            if ($queryCount > config('debug.performance.query_limit')) {
                Log::warning('Too many database queries', [
                    'url' => $request->fullUrl(),
                    'query_count' => $queryCount,
                    'duration' => round($duration, 2) . 'ms'
                ]);
            }
        }

        return $response;
    }
}