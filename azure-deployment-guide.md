# Azure VM Deployment and Troubleshooting Guide

## 🚨 Server Error Root Causes Found:

### 1. **Database Connection Error**
**Error**: `SQLSTATE[HY000] [1045] Access denied for user 'absensi'@'localhost'`

**Root Cause**: 
- .env file menggunakan credentials cPanel (`absensi_user`, `passwordku123`)
- Database `absensi` mungkin tidak exist di Azure VM
- MySQL user permissions tidak sesuai

### 2. **Environment Configuration Issues**
- APP_DEBUG=false (menyembunyikan error details)
- APP_URL menggunakan domain lama
- LOG_LEVEL=error (tidak cukup verbose)

---

## 🔧 **STEP-BY-STEP SOLUTION**

### **Step 1: Setup Database di Azure VM**

SSH ke Azure VM Anda dan jalankan:

```bash
# Login ke MySQL
sudo mysql -u root -p

# Buat database
CREATE DATABASE absensi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Buat user untuk Laravel
CREATE USER 'azureuser'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON absensi_db.* TO 'azureuser'@'localhost';
FLUSH PRIVILEGES;

# Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User='azureuser';
EXIT;
```

### **Step 2: Update Environment Configuration**

1. **Backup current .env:**
```bash
cp .env .env.backup
```

2. **Update .env untuk Azure VM:**
```bash
# Copy template Azure configuration
cp .env.azure .env

# Edit .env file:
nano .env
```

3. **Update these critical settings:**
```env
APP_ENV=production
APP_DEBUG=true              # Temporary untuk debugging
APP_URL=http://YOUR-AZURE-VM-IP

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=absensi_db
DB_USERNAME=azureuser        # Sesuai yang dibuat di Step 1
DB_PASSWORD=YourStrongPassword123!

LOG_CHANNEL=stack
LOG_LEVEL=debug              # Temporary verbose logging
```

### **Step 3: Clear Cache & Setup Laravel**

```bash
cd /var/www/html/uasabsensi  # Path sesuai instalasi Anda

# Clear all caches
php artisan optimize:clear

# Test database connection
php artisan migrate:status

# Run migrations if needed
php artisan migrate

# Set correct permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### **Step 4: Web Server Configuration**

**For Apache (.htaccess sudah ada):**
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Update Apache config for Laravel
sudo nano /etc/apache2/sites-available/000-default.conf
```

**Add to VirtualHost:**
```apache
DocumentRoot /var/www/html/uasabsensi/public

<Directory /var/www/html/uasabsensi/public>
    AllowOverride All
    Require all granted
</Directory>
```

```bash
# Restart Apache
sudo systemctl restart apache2
```

### **Step 5: PHP Requirements Check**

```bash
# Check PHP version (Laravel 12 requires PHP 8.2+)
php -v

# Check required PHP extensions
php -m | grep -E "(pdo|mysql|openssl|mbstring|tokenizer|xml|ctype|json|bcmath)"

# Install missing extensions if needed (Ubuntu/Debian):
sudo apt update
sudo apt install php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip
```

### **Step 6: Debug & Verify**

1. **Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

2. **Test database connection:**
```bash
php artisan tinker
# In tinker:
DB::connection()->getPdo();
```

3. **Test web access:**
```bash
curl -I http://localhost
# atau
wget --spider http://localhost
```

---

## 🔍 **Troubleshooting Common Issues**

### **Issue 1: Database Still Can't Connect**
```bash
# Verify MySQL is running
sudo systemctl status mysql

# Check if user can actually connect
mysql -u azureuser -p absensi_db

# Check Laravel database config
php artisan config:cache
php artisan config:show database
```

### **Issue 2: File Permissions**
```bash
# Fix Laravel permissions
sudo find /var/www/html/uasabsensi -type f -exec chmod 644 {} \;
sudo find /var/www/html/uasabsensi -type d -exec chmod 755 {} \;
sudo chown -R www-data:www-data /var/www/html/uasabsensi
sudo chmod -R 775 /var/www/html/uasabsensi/storage
sudo chmod -R 775 /var/www/html/uasabsensi/bootstrap/cache
```

### **Issue 3: Apache/Nginx Issues**
```bash
# Check error logs
sudo tail -f /var/log/apache2/error.log
# atau untuk Nginx:
sudo tail -f /var/log/nginx/error.log

# Test config
sudo apache2ctl configtest
# atau untuk Nginx:
sudo nginx -t
```

---

## ✅ **Final Verification Steps**

1. **Check application status:**
```bash
php artisan about
```

2. **Verify routes:**
```bash
php artisan route:list
```

3. **Check for any remaining errors:**
```bash
tail -n 50 storage/logs/laravel.log
```

4. **Access your application:**
- Browse to: `http://YOUR-AZURE-VM-IP`
- Should see Laravel welcome or login page

---

## 🎯 **Security Notes for Production**

After everything works, update .env:
```env
APP_DEBUG=false          # Disable debug in production
LOG_LEVEL=warning        # Reduce log verbosity
APP_KEY=YOUR_UNIQUE_KEY  # Generate new key if needed
```

And ensure proper firewall configuration for Azure VM.