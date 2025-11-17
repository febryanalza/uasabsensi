#!/bin/bash
# PRODUCTION DEPLOYMENT SCRIPT - Fix 500 Error

echo "🚀 DEPLOYING TO PRODUCTION - FIX 500 ERROR"
echo "=========================================="

# Step 1: Copy environment
echo "📄 Setting up environment..."
cp .env.production .env

# Step 2: Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear  
php artisan view:clear
php artisan route:clear

# Step 3: Test database connection
echo "🗄️ Testing database..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database: ✅ CONNECTED'; } catch (Exception \$e) { echo 'Database: ❌ ERROR - ' . \$e->getMessage(); }"

# Step 4: Check migrations
echo "📊 Checking migrations..."
php artisan migrate:status

# Step 5: Check required tables for session/cache
echo "🔍 Checking required tables..."
php artisan tinker --execute="
try {
    Schema::hasTable('sessions') ? print('Sessions table: ✅ EXISTS') : print('Sessions table: ❌ MISSING');
    echo PHP_EOL;
    Schema::hasTable('cache') ? print('Cache table: ✅ EXISTS') : print('Cache table: ❌ MISSING');
    echo PHP_EOL;
    Schema::hasTable('jobs') ? print('Jobs table: ✅ EXISTS') : print('Jobs table: ❌ MISSING');
} catch (Exception \$e) {
    echo 'Table check error: ' . \$e->getMessage();
}
"

# Step 6: Test view rendering
echo "🎨 Testing home view..."
php artisan tinker --execute="
try {
    view('home')->render();
    echo 'Home view: ✅ SUCCESS';
} catch (Exception \$e) {
    echo 'Home view: ❌ ERROR - ' . \$e->getMessage();
}
"

# Step 7: Set proper permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Step 8: Optimize for production
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ DEPLOYMENT COMPLETE!"
echo "💡 If still getting 500 error:"
echo "   1. Check web server error logs"
echo "   2. Verify .htaccess exists in public/"
echo "   3. Ensure DocumentRoot points to /public"
echo "   4. Check file permissions on storage/ and bootstrap/cache/"