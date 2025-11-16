<?php

return [
    'debug_errors' => [
        'enabled' => env('APP_DEBUG', false),
        'show_trace' => env('APP_ENV') === 'local',
        'log_queries' => env('LOG_QUERIES', false),
    ],

    'performance' => [
        'query_limit' => 50, // Alert if more than 50 queries per request
        'response_time_limit' => 1000, // Alert if response > 1 second
        'memory_limit' => '256M',
    ],

    'cache' => [
        'dashboard_ttl' => 300, // 5 minutes
        'api_ttl' => 60, // 1 minute
        'static_ttl' => 3600, // 1 hour
    ],

    'database' => [
        'connection_timeout' => 10,
        'retry_attempts' => 3,
        'slow_query_threshold' => 500, // milliseconds
    ],
];