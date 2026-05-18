# Setup Guide - Toko Buku Naisya

Panduan lengkap untuk setup aplikasi Toko Buku Naisya dari awal.

## 📋 Prerequisites

Pastikan sistem Anda sudah terinstall:

- **PHP 8.2 atau lebih tinggi**
  ```bash
  php -v
  ```

- **Composer**
  ```bash
  composer -V
  ```

- **MySQL/MariaDB**
  ```bash
  mysql --version
  ```

- **Node.js & NPM**
  ```bash
  node -v
  npm -v
  ```

- **Git**
  ```bash
  git --version
  ```

## 🚀 Quick Start

### Opsi 1: Menggunakan Setup Script (Recommended)

#### Windows (PowerShell):
```powershell
powershell -ExecutionPolicy Bypass -File setup.ps1
```

#### Linux/Mac (Bash):
```bash
chmod +x setup.sh
./setup.sh
```

### Opsi 2: Manual Setup

#### 1. Clone Repository

```bash
git clone <repository-url>
cd Laravel_naisya-main
```

#### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

#### 3. Setup Environment File

```bash
# Copy .env.example ke .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4. Konfigurasi Database

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_naisya
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### 5. Buat Database

```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE laravel_naisya;
exit;
```

#### 6. Jalankan Migration

```bash
php artisan migrate
```

#### 7. (Optional) Seed Data Demo

```bash
php artisan db:seed --class=DemoTokoSeeder
```

#### 8. Build Assets

```bash
# Production build
npm run build

# Development build dengan hot reload
npm run dev
```

#### 9. Set Permissions (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
```

#### 10. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser: `http://127.0.0.1:8000`

## 🔐 Konfigurasi Midtrans

### 1. Daftar Akun Midtrans Sandbox

1. Kunjungi: https://dashboard.sandbox.midtrans.com/register
2. Isi form registrasi
3. Verifikasi email Anda
4. Login ke dashboard

### 2. Dapatkan API Keys

1. Login ke Midtrans Sandbox Dashboard
2. Klik menu **Settings** di sidebar
3. Pilih **Access Keys**
4. Copy **Server Key** dan **Client Key**

### 3. Update File .env

Edit file `.env` dan tambahkan/update:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_ENABLED_PAYMENTS=qris
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

### 4. Aktifkan Payment Channel QRIS

1. Di Midtrans Dashboard, buka **Settings**
2. Pilih **Snap Preferences**
3. Klik tab **Payment Channels**
4. Aktifkan **QRIS**
5. Klik **Save**

### 5. Setup Webhook untuk Development

Untuk menerima notifikasi pembayaran di localhost:

#### Install ngrok

**Windows:**
1. Download dari: https://ngrok.com/download
2. Extract file zip
3. Jalankan `ngrok.exe`

**Linux/Mac:**
```bash
# Via Homebrew (Mac)
brew install ngrok

# Via Snap (Linux)
snap install ngrok
```

#### Jalankan ngrok

```bash
ngrok http 8000
```

Output akan seperti ini:
```
Forwarding  https://abc123.ngrok-free.app -> http://localhost:8000
```

#### Set Notification URL di Midtrans

1. Copy URL ngrok (contoh: `https://abc123.ngrok-free.app`)
2. Login ke Midtrans Dashboard
3. Buka **Settings > Configuration**
4. Di bagian **Payment Notification URL**, isi:
   ```
   https://abc123.ngrok-free.app/payments/midtrans/notification
   ```
5. Klik **Save**

#### Update APP_URL di .env

```env
APP_URL=https://abc123.ngrok-free.app
```

**Catatan:** URL ngrok akan berubah setiap kali Anda restart. Untuk URL tetap, gunakan ngrok berbayar atau alternatif seperti localtunnel.

## 👤 Membuat User Admin

### Opsi 1: Via Tinker

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('password123');
$user->role = 'admin';
$user->save();
```

### Opsi 2: Via Database Seeder

Buat file `database/seeders/AdminSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'User Demo',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}
```

Jalankan seeder:

```bash
php artisan db:seed --class=AdminSeeder
```

## 🧪 Testing Setup

### 1. Test Database Connection

```bash
php artisan migrate:status
```

### 2. Test Aplikasi

```bash
php artisan test
```

### 3. Test Midtrans Configuration

1. Login sebagai admin
2. Tambahkan buku di menu **Master Buku Toko**
3. Logout dan login sebagai user
4. Buka menu **Checkout Toko Buku**
5. Tambahkan buku ke keranjang
6. Klik **Bayar dengan QRIS Midtrans**
7. Jika popup Midtrans muncul, konfigurasi berhasil!

## 🔧 Troubleshooting

### Error: "No application encryption key"

```bash
php artisan key:generate
```

### Error: "SQLSTATE[HY000] [1045] Access denied"

Periksa kredensial database di file `.env`:
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_DATABASE`

### Error: "Class 'App\...' not found"

```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear
```

### Error: Permission denied (storage)

**Linux/Mac:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Windows:**
Pastikan folder `storage` dan `bootstrap/cache` memiliki write permission.

### Midtrans: "No payment channels available"

1. Login ke Midtrans Dashboard
2. **Settings > Snap Preferences > Payment Channels**
3. Aktifkan **QRIS**
4. Tunggu beberapa menit untuk propagasi
5. Clear cache aplikasi: `php artisan cache:clear`

### Webhook tidak masuk

1. Pastikan ngrok masih berjalan
2. Periksa URL di Midtrans Dashboard
3. Test webhook manual:
   ```bash
   curl -X POST http://localhost:8000/payments/midtrans/notification \
     -H "Content-Type: application/json" \
     -d '{"order_id":"TEST-001","transaction_status":"settlement"}'
   ```

### Assets tidak muncul

```bash
# Clear cache
php artisan cache:clear
php artisan view:clear

# Rebuild assets
npm run build

# Atau untuk development
npm run dev
```

## 📊 Database Schema

### Users Table
- `id`: Primary key
- `name`: Nama user
- `email`: Email (unique)
- `password`: Password (hashed)
- `role`: 'admin' atau 'user'
- `is_guest`: Boolean untuk guest user
- `email_verified_at`: Timestamp verifikasi email

### Barang Table
- `id_barang`: Primary key
- `nama_barang`: Nama barang
- `harga`: Harga barang
- `stok`: Jumlah stok
- `vendor_id`: Foreign key ke users (admin)
- `is_active`: Boolean status aktif

### Penjualans Table
- `id`: Primary key
- `nomor_transaksi`: Nomor transaksi (unique)
- `user_id`: Foreign key ke users
- `total`: Total pembayaran
- `status`: Status pembayaran
- `midtrans_order_id`: Order ID Midtrans
- `midtrans_transaction_id`: Transaction ID Midtrans
- `payment_type`: Tipe pembayaran
- `payment_status`: Status dari Midtrans

## 🔄 Update Aplikasi

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rebuild assets
npm run build
```

## 📝 Environment Variables Reference

### Application
- `APP_NAME`: Nama aplikasi
- `APP_ENV`: Environment (local/production)
- `APP_KEY`: Encryption key (auto-generated)
- `APP_DEBUG`: Debug mode (true/false)
- `APP_URL`: URL aplikasi

### Database
- `DB_CONNECTION`: mysql
- `DB_HOST`: 127.0.0.1
- `DB_PORT`: 3306
- `DB_DATABASE`: Nama database
- `DB_USERNAME`: Username MySQL
- `DB_PASSWORD`: Password MySQL

### Midtrans
- `MIDTRANS_SERVER_KEY`: Server key dari Midtrans
- `MIDTRANS_CLIENT_KEY`: Client key dari Midtrans
- `MIDTRANS_IS_PRODUCTION`: false untuk sandbox
- `MIDTRANS_ENABLED_PAYMENTS`: qris (atau comma-separated)

### OTP
- `OTP_EXPIRY_MINUTES`: Durasi OTP valid (default: 5)
- `OTP_LENGTH`: Panjang kode OTP (default: 6)

### Features
- `ENABLE_REGISTRATION`: Aktifkan registrasi (true/false)
- `ENABLE_GUEST_CHECKOUT`: Aktifkan guest checkout (true/false)

## 🎯 Next Steps

Setelah setup selesai:

1. **Baca dokumentasi Midtrans**: [docs/midtrans-kantin-demo.md](midtrans-kantin-demo.md)
2. **Customize aplikasi** sesuai kebutuhan
3. **Setup production environment** untuk deployment
4. **Configure backup** untuk database
5. **Setup monitoring** dan logging

## 📞 Bantuan

Jika mengalami masalah:

1. Periksa log Laravel: `storage/logs/laravel.log`
2. Periksa log web server (Apache/Nginx)
3. Buka issue di repository
4. Baca dokumentasi Laravel: https://laravel.com/docs

## 🔗 Useful Links

- [Laravel Documentation](https://laravel.com/docs)
- [Midtrans Documentation](https://docs.midtrans.com)
- [Midtrans Sandbox Dashboard](https://dashboard.sandbox.midtrans.com)
- [ngrok Documentation](https://ngrok.com/docs)
