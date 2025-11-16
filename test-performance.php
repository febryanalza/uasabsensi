<?php

// Quick performance test script for Laravel
// Run with: php artisan tinker < test-performance.php

echo "=== LARAVEL PERFORMANCE TEST ===\n";

// Test 1: Database Connection
echo "1. Testing database connection...\n";
try {
    $startTime = microtime(true);
    $result = DB::select('SELECT 1 as test');
    $dbTime = (microtime(true) - $startTime) * 1000;
    echo "   ✅ Database OK - {$dbTime}ms\n";
} catch (Exception $e) {
    echo "   ❌ Database FAILED: " . $e->getMessage() . "\n";
}

// Test 2: Cache Performance
echo "2. Testing cache performance...\n";
try {
    $startTime = microtime(true);
    Cache::put('test_key', 'test_value', 60);
    $value = Cache::get('test_key');
    $cacheTime = (microtime(true) - $startTime) * 1000;
    echo "   ✅ Cache OK - {$cacheTime}ms\n";
} catch (Exception $e) {
    echo "   ❌ Cache FAILED: " . $e->getMessage() . "\n";
}

// Test 3: View Rendering
echo "3. Testing view rendering...\n";
try {
    $startTime = microtime(true);
    $view = view('welcome')->render();
    $viewTime = (microtime(true) - $startTime) * 1000;
    echo "   ✅ View rendering OK - {$viewTime}ms\n";
} catch (Exception $e) {
    echo "   ❌ View rendering FAILED: " . $e->getMessage() . "\n";
}

// Test 4: Route Resolution
echo "4. Testing route resolution...\n";
try {
    $startTime = microtime(true);
    $route = Route::getRoutes()->match(Request::create('/', 'GET'));
    $routeTime = (microtime(true) - $startTime) * 1000;
    echo "   ✅ Route resolution OK - {$routeTime}ms\n";
} catch (Exception $e) {
    echo "   ❌ Route resolution FAILED: " . $e->getMessage() . "\n";
}

// Test 5: Memory Usage
echo "5. Memory usage check...\n";
$memory = round(memory_get_usage(true) / 1024 / 1024, 2);
$memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
echo "   Current memory: {$memory}MB\n";
echo "   Peak memory: {$memoryPeak}MB\n";

// Test 6: Configuration
echo "6. Configuration check...\n";
echo "   APP_ENV: " . config('app.env') . "\n";
echo "   APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
echo "   DB_CONNECTION: " . config('database.default') . "\n";
echo "   CACHE_STORE: " . config('cache.default') . "\n";

echo "\n=== TEST COMPLETED ===\n";