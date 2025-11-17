# CRITICAL: Fix 500 Server Error - Local dan VM Server

## Masalah yang Diperbaiki:
1. ✅ **Route Issue**: Root URL (/) sekarang menuju ke `home.blade.php` bukan `welcome.blade.php`
2. ✅ **Database Local**: Kredensial database diubah untuk development lokal
3. 🔄 **VM Server**: Perlu setup database dan environment di VM

## Perubahan yang Dilakukan:

### 1. Route Web (routes/web.php)
```php
// ROOT URL sekarang menuju home.blade.php
Route::get('/', function () {
    try {
        return view('home'); // Main company homepage
    } catch (\Exception $e) {
        Log::error('Homepage error: ' . $e->getMessage());
        return response('Server temporarily unavailable', 503);
    }
})->name('home');
```

### 2. Environment Local (.env)
```env
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=absensi
DB_USERNAME=root
DB_PASSWORD=
```

## LANGKAH UNTUK VM SERVER:

### A. Setup Database di VM Azure:
```bash
# 1. Login ke MySQL sebagai root
sudo mysql -u root -p

# 2. Buat database dan user
CREATE DATABASE absensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'passwordku123';
GRANT ALL PRIVILEGES ON absensi.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### B. Environment VM (.env di VM):
```env
APP_NAME="Sistem Absensi"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=absensi
DB_USERNAME=absensi_user
DB_PASSWORD=passwordku123

CACHE_STORE=file
SESSION_DRIVER=file
LOG_LEVEL=error
```

### C. Deploy ke VM Server:
```bash
# 1. Upload kode ke server
# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache

# 4. Setup environment
cp .env.production .env
php artisan key:generate
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Run migrations
php artisan migrate --force

# 6. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### D. Apache Virtual Host (jika diperlukan):
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/absensi/public
    
    <Directory /var/www/absensi/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/absensi_error.log
    CustomLog ${APACHE_LOG_DIR}/absensi_access.log combined
</VirtualHost>
```

## TESTING:

### Local Testing:
```bash
# 1. Test database
php artisan tinker --execute="include 'test-db.php';"

# 2. Test routes
php artisan route:list --name=home

# 3. Start server
php artisan serve
```

### VM Testing:
```bash
# 1. Check database connection
php artisan tinker --execute="\\DB::connection()->getPdo(); echo 'Connected!';"

# 2. Check routes work
curl -I http://localhost/

# 3. Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/apache2/absensi_error.log
```

## DEBUGGING:

### Jika masih error 500:
```bash
# 1. Enable detailed errors (sementara)
APP_DEBUG=true dalam .env

# 2. Check logs
tail -f storage/logs/laravel.log

# 3. Check permissions
ls -la storage/
ls -la bootstrap/cache/

# 4. Check database
php artisan migrate:status
```

## CHECKLIST:
- [ ] Database user 'absensi_user' dibuat di VM
- [ ] File .env di VM menggunakan kredensial yang benar
- [ ] Permissions storage dan bootstrap/cache: 755
- [ ] Owner: www-data (atau user web server)
- [ ] Apache/Nginx DocumentRoot ke /public
- [ ] AllowOverride All di Apache
- [ ] SSL Certificate (jika HTTPS)

## CATATAN PENTING:
1. **Root URL** sekarang langsung menuju halaman utama company (home.blade.php)
2. **Database local** menggunakan root tanpa password (default XAMPP/WAMP)
3. **VM server** perlu setup database sesuai kredensial di .env.production
4. **APP_DEBUG=false** di production untuk keamanan