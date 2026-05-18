# Deployment Guide - Toko Buku Naisya

Panduan untuk deploy aplikasi ke production server.

## 📋 Pre-Deployment Checklist

- [ ] Semua fitur sudah ditest di local/staging
- [ ] Database backup sudah tersedia
- [ ] Environment variables production sudah disiapkan
- [ ] SSL certificate sudah tersedia
- [ ] Domain sudah pointing ke server
- [ ] Midtrans production keys sudah didapat
- [ ] Server requirements terpenuhi

## 🖥️ Server Requirements

### Minimum Requirements
- PHP 8.2 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Composer
- Node.js 18+ & NPM
- Web Server (Apache/Nginx)
- SSL Certificate (Let's Encrypt recommended)

### Recommended Server Specs
- CPU: 2 cores
- RAM: 2GB minimum, 4GB recommended
- Storage: 20GB SSD
- Bandwidth: Unlimited

### PHP Extensions Required
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD or Imagick
```

## 🚀 Deployment Steps

### 1. Persiapan Server

#### Update System
```bash
sudo apt update
sudo apt upgrade -y
```

#### Install PHP 8.2
```bash
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-gd php8.2-zip -y
```

#### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Install Node.js & NPM
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

#### Install MySQL
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

#### Install Nginx
```bash
sudo apt install nginx -y
```

### 2. Setup Database

```bash
# Login ke MySQL
sudo mysql -u root -p

# Buat database dan user
CREATE DATABASE laravel_naisya_prod;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON laravel_naisya_prod.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clone & Setup Aplikasi

```bash
# Buat direktori untuk aplikasi
sudo mkdir -p /var/www/toko-buku
cd /var/www/toko-buku

# Clone repository
sudo git clone <repository-url> .

# Set ownership
sudo chown -R www-data:www-data /var/www/toko-buku
sudo chmod -R 755 /var/www/toko-buku

# Set permissions untuk storage dan cache
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Install Dependencies

```bash
# Install PHP dependencies (production only)
composer install --optimize-autoloader --no-dev

# Install JavaScript dependencies
npm ci --production

# Build assets
npm run build
```

### 5. Setup Environment

```bash
# Copy .env.example
cp .env.example .env

# Edit .env untuk production
nano .env
```

**Production .env Configuration:**

```env
APP_NAME="Toko Buku Naisya"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://yourdomain.com
APP_LOCALE=id

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_naisya_prod
DB_USERNAME=laravel_user
DB_PASSWORD=strong_password_here

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database

# Midtrans Production
MIDTRANS_SERVER_KEY=Mid-server-PRODUCTION_KEY
MIDTRANS_CLIENT_KEY=Mid-client-PRODUCTION_KEY
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_ENABLED_PAYMENTS=qris

# Mail Configuration (Production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Generate Key & Run Migrations

```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# (Optional) Seed data
php artisan db:seed --class=DemoTokoSeeder

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/toko-buku
```

**Nginx Configuration:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /var/www/toko-buku/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    # Logging
    access_log /var/log/nginx/toko-buku-access.log;
    error_log /var/log/nginx/toko-buku-error.log;

    # Max upload size
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Enable Site:**

```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/toko-buku /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 8. Setup SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### 9. Setup Queue Worker (Optional)

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

**Supervisor Configuration:**

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/toko-buku/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/toko-buku/storage/logs/worker.log
stopwaitsecs=3600
```

**Start Supervisor:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 10. Setup Cron Jobs

```bash
sudo crontab -e -u www-data
```

**Add this line:**

```cron
* * * * * cd /var/www/toko-buku && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Configure Midtrans Production

1. Login ke Midtrans Production Dashboard: https://dashboard.midtrans.com
2. Buka **Settings > Access Keys**
3. Copy Production Server Key dan Client Key
4. Update `.env` dengan production keys
5. Buka **Settings > Configuration**
6. Set **Payment Notification URL**:
   ```
   https://yourdomain.com/payments/midtrans/notification
   ```
7. Aktifkan payment channels yang diinginkan

### 12. Final Steps

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
sudo chown -R www-data:www-data /var/www/toko-buku
sudo chmod -R 755 /var/www/toko-buku
sudo chmod -R 775 /var/www/toko-buku/storage
sudo chmod -R 775 /var/www/toko-buku/bootstrap/cache
```

## 🔒 Security Hardening

### 1. Firewall Configuration

```bash
# Install UFW
sudo apt install ufw

# Allow SSH, HTTP, HTTPS
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443

# Enable firewall
sudo ufw enable
```

### 2. Disable Directory Listing

Already handled in Nginx config with `autoindex off;`

### 3. Hide PHP Version

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
expose_php = Off
```

### 4. Secure MySQL

```bash
sudo mysql_secure_installation
```

### 5. Regular Updates

```bash
# Create update script
sudo nano /usr/local/bin/update-system.sh
```

```bash
#!/bin/bash
apt update
apt upgrade -y
apt autoremove -y
```

```bash
sudo chmod +x /usr/local/bin/update-system.sh
```

## 📊 Monitoring & Logging

### 1. Setup Log Rotation

```bash
sudo nano /etc/logrotate.d/laravel
```

```
/var/www/toko-buku/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 2. Monitor Application

```bash
# Check application logs
tail -f /var/www/toko-buku/storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/toko-buku-error.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

## 🔄 Deployment Updates

### Zero-Downtime Deployment Script

Create `deploy.sh`:

```bash
#!/bin/bash

echo "Starting deployment..."

# Enable maintenance mode
php artisan down

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev
npm ci --production
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart laravel-worker:*

# Disable maintenance mode
php artisan up

echo "Deployment completed!"
```

```bash
chmod +x deploy.sh
```

## 🔙 Backup Strategy

### 1. Database Backup Script

```bash
sudo nano /usr/local/bin/backup-db.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="laravel_naisya_prod"
DB_USER="laravel_user"
DB_PASS="strong_password_here"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-db.sh
```

### 2. Schedule Backups

```bash
sudo crontab -e
```

```cron
# Daily database backup at 2 AM
0 2 * * * /usr/local/bin/backup-db.sh >> /var/log/backup.log 2>&1
```

## 🧪 Post-Deployment Testing

1. **Test Homepage**: https://yourdomain.com
2. **Test Login**: Login dengan akun admin dan user
3. **Test Checkout**: Buat transaksi test
4. **Test Payment**: Lakukan pembayaran test di Midtrans
5. **Test Webhook**: Verifikasi notifikasi pembayaran masuk
6. **Test SSL**: https://www.ssllabs.com/ssltest/
7. **Test Performance**: https://pagespeed.web.dev/

## 📞 Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error log
sudo tail -f /var/log/nginx/toko-buku-error.log

# Check PHP-FPM log
sudo tail -f /var/log/php8.2-fpm.log

# Check permissions
sudo chown -R www-data:www-data /var/www/toko-buku
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Error

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql
```

### Assets Not Loading

```bash
# Rebuild assets
npm run build

# Clear cache
php artisan cache:clear
php artisan view:clear

# Check Nginx config
sudo nginx -t
```

## 🔗 Useful Commands

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# View logs
sudo journalctl -u nginx -f
sudo journalctl -u php8.2-fpm -f
```

## 📚 Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Midtrans Production Guide](https://docs.midtrans.com/en/after-payment/overview)
