# 🚨 CRITICAL PERFORMANCE FIXES FOR AZURE VM

## ⚡ IMMEDIATE ACTIONS REQUIRED:

### **1. 🔴 DATABASE OPTIMIZATION (328ms → Target: <50ms)**

```sql
-- Connect to MySQL and run these optimizations:
mysql -u azureuser -p

-- Add missing indexes
USE absensi_db;

-- Check current indexes
SHOW INDEX FROM users;
SHOW INDEX FROM karyawan;
SHOW INDEX FROM absensi;

-- Add performance indexes if missing
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE karyawan ADD INDEX idx_user_id (user_id);
ALTER TABLE karyawan ADD INDEX idx_status (status);
ALTER TABLE absensi ADD INDEX idx_karyawan_tanggal (karyawan_id, tanggal);
ALTER TABLE absensi ADD INDEX idx_tanggal (tanggal);
ALTER TABLE absensi ADD INDEX idx_jam_masuk (jam_masuk);

-- Optimize tables
OPTIMIZE TABLE users, karyawan, absensi, gaji;

-- Check query performance
EXPLAIN SELECT * FROM absensi WHERE karyawan_id = 1 AND tanggal >= '2025-01-01';
```

### **2. 🔶 CACHE OPTIMIZATION (280ms → Target: <10ms)**

```bash
# Switch to Redis for better performance (if available)
sudo apt update
sudo apt install redis-server php-redis

# Or optimize file cache permissions
sudo chown -R www-data:www-data /var/www/html/uasabsensi/storage/framework/cache
sudo chmod -R 775 /var/www/html/uasabsensi/storage/framework/cache

# Update .env for Redis (if installed):
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### **3. 🔶 VIEW RENDERING OPTIMIZATION (416ms → Target: <100ms)**

```bash
# Precompile all views
cd /var/www/html/uasabsensi
php artisan view:cache

# Enable OPcache for PHP
echo "opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.enable_cli=1" | sudo tee -a /etc/php/8.2/apache2/php.ini

sudo systemctl restart apache2
```

---

## 🛠️ **AZURE VM SPECIFIC FIXES:**

### **1. MySQL Configuration Tuning**
```bash
# Edit MySQL config
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add these optimizations:
[mysqld]
innodb_buffer_pool_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_size = 64M
query_cache_type = 1
tmp_table_size = 64M
max_heap_table_size = 64M
thread_cache_size = 50
table_open_cache = 2000
innodb_thread_concurrency = 0

sudo systemctl restart mysql
```

### **2. Apache Performance Tuning**
```bash
# Enable Apache modules
sudo a2enmod rewrite expires deflate headers

# Update Apache config
sudo nano /etc/apache2/sites-available/000-default.conf

# Add inside VirtualHost:
<Directory /var/www/html/uasabsensi/public>
    # Enable compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript
    </IfModule>
    
    # Browser caching
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresDefault "access plus 1 month"
        ExpiresByType text/css "access plus 1 year"
        ExpiresByType application/javascript "access plus 1 year"
    </IfModule>
    
    # Disable .htaccess for performance
    AllowOverride None
    
    # Manual rewrite rules (copy from .htaccess)
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</Directory>

sudo systemctl restart apache2
```

### **3. PHP-FPM Optimization (if available)**
```bash
# Install PHP-FPM
sudo apt install php8.2-fpm

# Configure PHP-FPM pool
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Update these settings:
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

sudo systemctl restart php8.2-fpm
```

---

## 🎯 **FINAL VERIFICATION STEPS:**

### **1. Re-run Performance Test**
```bash
cd /var/www/html/uasabsensi
php artisan tinker --execute="include 'test-performance.php';"

# Target results:
# Database: <50ms
# Cache: <10ms  
# View: <100ms
```

### **2. Load Test Homepage**
```bash
# Test response time
curl -w "@curl-format.txt" -o /dev/null -s http://YOUR-AZURE-VM-IP/

# Should be under 200ms total
```

### **3. Monitor Logs**
```bash
# Watch for slow queries
sudo tail -f /var/log/mysql/slow-query.log

# Monitor Laravel logs
tail -f /var/www/html/uasabsensi/storage/logs/laravel.log
```

---

## 🚨 **EMERGENCY QUICK FIXES:**

If above takes time, apply these immediate fixes:

```bash
# 1. Use Redis for sessions (faster than files)
echo "SESSION_DRIVER=database" >> .env

# 2. Disable query logging in production
echo "LOG_QUERIES=false" >> .env

# 3. Enable asset versioning
php artisan storage:link

# 4. Clear everything and recompile
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart services
sudo systemctl restart mysql apache2
```

Apply these fixes in order and test after each step. The 500ms response time should drop to under 200ms.