# Toko Buku Naisya - Laravel Application

Aplikasi Toko Buku dengan integrasi Midtrans Payment Gateway, sistem OTP verification, dan manajemen inventory.

## 🚀 Fitur Utama

- **Autentikasi & OTP Verification**: Login dengan verifikasi OTP
- **Role-Based Access Control**: Admin dan User dengan hak akses berbeda
- **Payment Gateway**: Integrasi Midtrans untuk pembayaran QRIS
- **Inventory Management**: Manajemen barang, kategori, dan buku
- **Barcode/QR Scanner**: Scanner untuk barang dan pesanan
- **Order Management**: Tracking pesanan dan status pembayaran
- **Customer Management**: Manajemen data customer
- **Document Management**: Upload dan manajemen dokumen

## 📋 Requirements

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Node.js & NPM
- Laravel 12.x

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd Laravel_naisya-main
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

```bash
# Copy file .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_naisya
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Buat Database

```bash
# Buat database MySQL
mysql -u root -p
CREATE DATABASE laravel_naisya;
exit;
```

### 6. Jalankan Migration

```bash
php artisan migrate
```

### 7. (Optional) Seed Data Demo

```bash
# Seed data demo untuk toko buku
php artisan db:seed --class=DemoTokoSeeder
```

### 8. Build Assets

```bash
npm run build
# atau untuk development
npm run dev
```

### 9. Jalankan Aplikasi

```bash
php artisan serve
```

Aplikasi akan berjalan di: `http://127.0.0.1:8000`

## 🔑 Konfigurasi Midtrans

### 1. Daftar Akun Midtrans Sandbox

Kunjungi: https://dashboard.sandbox.midtrans.com/register

### 2. Dapatkan API Keys

Setelah login, buka **Settings > Access Keys**:
- Server Key
- Client Key

### 3. Update .env

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_ENABLED_PAYMENTS=qris
```

### 4. Setup Webhook (Optional untuk Development)

Untuk menerima notifikasi pembayaran di localhost, gunakan ngrok:

```bash
# Install ngrok
# Download dari: https://ngrok.com/download

# Jalankan ngrok
ngrok http 8000
```

Copy URL ngrok (contoh: `https://abc123.ngrok-free.app`) dan set di Midtrans Dashboard:

**Settings > Configuration > Payment Notification URL:**
```
https://abc123.ngrok-free.app/payments/midtrans/notification
```

## 👥 User Roles

### Admin
- Manajemen master data (barang, kategori, buku)
- Melihat dan mengelola pesanan vendor
- Manajemen customer
- Upload dokumen
- Cetak label barang

### User (Customer)
- Browse dan checkout buku
- Melihat riwayat pesanan
- Pembayaran via Midtrans
- Tracking status pesanan

## 📱 Cara Penggunaan

### Untuk Admin

1. Login dengan akun admin
2. Buka menu **Master Buku Toko**
3. Tambahkan buku baru dan aktifkan
4. Buku aktif akan muncul di halaman checkout user

### Untuk User/Customer

1. Login dengan akun user
2. Buka menu **Checkout Toko Buku**
3. Cari buku dengan kode atau gunakan fitur cepat
4. Tambahkan ke keranjang
5. Checkout dan bayar via QRIS Midtrans
6. Lihat status pesanan di halaman detail order

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan feature test
php vendor/bin/phpunit --testsuite Feature

# Jalankan test dengan coverage
php artisan test --coverage
```

## 📁 Struktur Project

```
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controllers
│   │   └── Middleware/       # Custom middleware
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic services
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── views/                # Blade templates
│   └── js/                   # JavaScript files
├── routes/
│   └── web.php               # Web routes
└── storage/                  # Storage files
```

## 🔐 Security

- CSRF Protection aktif untuk semua form
- Password hashing dengan bcrypt
- OTP verification untuk autentikasi tambahan
- Role-based middleware untuk authorization
- Input validation dan sanitization

## 🐛 Troubleshooting

### Error: "No application encryption key has been specified"

```bash
php artisan key:generate
```

### Error: Database connection refused

Pastikan MySQL service berjalan dan kredensial di `.env` benar.

### Error: "Class not found"

```bash
composer dump-autoload
```

### Error: Permission denied (storage/logs)

```bash
chmod -R 775 storage bootstrap/cache
```

### Midtrans: "No payment channels available"

1. Login ke Midtrans Dashboard
2. Buka **Settings > Snap Preferences > Payment Channels**
3. Aktifkan QRIS

## 📝 Environment Variables

Lihat file `.env.example` untuk daftar lengkap environment variables yang tersedia.

### Penting untuk Diisi:

- `APP_KEY`: Generate dengan `php artisan key:generate`
- `DB_*`: Konfigurasi database
- `MIDTRANS_*`: API keys dari Midtrans
- `APP_URL`: URL aplikasi (penting untuk webhook)

## 🤝 Contributing

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

This project is licensed under the MIT License.

## 📞 Support

Untuk bantuan dan pertanyaan, silakan buka issue di repository ini.

## 🔗 Links

- [Laravel Documentation](https://laravel.com/docs)
- [Midtrans Documentation](https://docs.midtrans.com)
- [Midtrans Sandbox Dashboard](https://dashboard.sandbox.midtrans.com)

## 📚 Additional Documentation

- [Midtrans Integration Guide](docs/midtrans-kantin-demo.md)
