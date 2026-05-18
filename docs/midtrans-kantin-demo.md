# Midtrans Toko Buku Demo

Panduan ini menjelaskan cara menyiapkan dan mendemokan modul toko buku dengan payment gateway Midtrans yang sudah diintegrasikan ke project Laravel ini.

## 1. Isi Konfigurasi Midtrans di `.env`

Tambahkan atau sesuaikan variabel berikut:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_ENABLED_PAYMENTS=qris
```

Catatan:

- Gunakan `SB-...` untuk sandbox.
- Ganti `MIDTRANS_IS_PRODUCTION=true` hanya saat sudah memakai key production.

## 2. Jalankan Migration

```powershell
php artisan migrate
```

Migration baru akan:

- menambah flag `is_guest` di tabel `users`
- menambah relasi `vendor_id` dan `is_active` di tabel `barang`
- menambah metadata pembayaran Midtrans di tabel `penjualans`

## 3. Siapkan Akun Admin

Di project ini, akun pengelola master buku memakai role `admin`.

Jika Anda belum punya akun admin/vendor, buat dari seeder atau langsung insert manual ke tabel `users`.

## 3A. Siapkan Data Buku Demo Otomatis

Supaya Anda tidak perlu input menu satu per satu saat latihan demo, jalankan:

```powershell
php artisan db:seed --class=DemoTokoSeeder
```

Seeder ini akan:

- mengambil semua user dengan role `admin`
- membuat beberapa buku demo aktif untuk setiap admin
- menyiapkan data yang langsung tampil di halaman checkout user

## 4. Jalankan Aplikasi

```powershell
php artisan serve
```

Halaman login:

- `http://127.0.0.1:8000/login`

## 5. Siapkan Master Buku

Login sebagai admin lalu buka:

- `Master Buku Toko`

Langkah:

1. Tambahkan beberapa buku.
2. Pastikan checkbox **buku aktif** dicentang.
3. Harga buku harus angka valid.

Buku aktif inilah yang akan tampil di halaman checkout user.

## 6. Demo User Checkout

Login sebagai user lalu buka menu `Checkout Toko Buku`, kemudian lakukan urutan ini:

1. Cari buku dengan kode, atau klik `Gunakan Cepat`.
2. Masukkan jumlah.
3. Klik `Tambah ke Keranjang`.
4. Klik `Bayar dengan QRIS Midtrans`.

Saat checkout:

- order dibuat atas user yang sedang login
- order dibuat ke tabel `penjualans`
- item order dibuat ke tabel `penjualan_items`
- aplikasi meminta Snap token ke Midtrans
- popup Snap dibuka untuk pembayaran QRIS

## 7. Demo Pembayaran Sandbox Midtrans

Di popup Snap:

1. Scan QRIS dari aplikasi pembayaran yang mendukung QRIS.
2. Ikuti instruksi pembayaran sandbox dari Midtrans.
3. Setelah popup selesai atau ditutup, aplikasi akan mengarahkan ke halaman detail order.

Di halaman detail order:

- status order tampil
- ada tombol `Periksa Status`
- jika webhook belum aktif, tombol ini bisa dipakai untuk sinkronisasi manual ke Midtrans

Jika popup menampilkan `No payment channels available`, artinya channel QRIS belum tersedia untuk akun Anda. Cek:

1. `Settings > Snap Preferences > Payment Channels`, pastikan QRIS aktif.
2. Bila QRIS belum aktif di akun merchant, ajukan aktivasi payment method di dashboard Midtrans.

## 8. Agar Webhook Midtrans Benar-Benar Masuk ke Localhost

Karena Midtrans tidak bisa mengirim notifikasi ke `localhost`, gunakan tunnel publik.

Contoh dengan `ngrok`:

```powershell
ngrok http 8000
```

Setelah dapat URL publik misalnya:

```text
https://abc123.ngrok-free.app
```

Lakukan ini:

1. Update `APP_URL` di `.env` menjadi URL tunnel bila perlu.
2. Login ke Midtrans MAP sandbox.
3. Buka `Settings > Configuration`.
4. Isi `Payment Notification URL` dengan:

```text
https://abc123.ngrok-free.app/payments/midtrans/notification
```

5. Simpan konfigurasi.

Setelah itu, saat status pembayaran berubah, Midtrans akan POST ke endpoint webhook Laravel dan status order akan otomatis ikut berubah.

## 9. Demo Panel Admin

Login admin lalu buka:

- `Master Buku Toko`

Yang bisa Anda tunjukkan saat demo:

1. Daftar buku yang menjadi sumber data POS user.
2. Buku aktif langsung muncul di halaman checkout user.
3. Detail pembayaran order user.
4. Perubahan status setelah webhook atau setelah klik `Periksa Status`.

## 10. Urutan Demo yang Paling Aman Saat Presentasi

Gunakan urutan ini:

1. Login admin dan tunjukkan `Master Buku Toko`.
2. Tambah 1-2 buku aktif.
3. Logout lalu login sebagai user.
4. Buka `Checkout Toko Buku`.
5. Tambahkan buku ke keranjang.
6. Checkout ke QRIS Midtrans.
7. Tunjukkan halaman detail order masih `Menunggu Pembayaran`.
8. Selesaikan pembayaran sandbox.
9. Klik `Periksa Status` bila webhook belum masuk.
10. Tunjukkan status berubah menjadi `Lunas`.

## 10A. Script Bicara Demo Singkat

Kalau Anda ingin presentasi lebih rapi, ini alur narasi yang bisa dipakai:

1. "Sistem ini memakai 2 sisi, yaitu admin dan user."
2. "Admin mengelola master buku toko."
3. "User login masuk ke halaman POS untuk checkout buku."
4. "Daftar buku di POS diambil langsung dari master admin."
5. "Saat checkout, sistem meminta Snap token ke Midtrans."
6. "Pembayaran dibatasi ke QRIS saja agar demo lebih sederhana."
7. "Ketika Midtrans mengirim notifikasi atau saat saya sinkronkan manual, status order berubah menjadi lunas."

## 10B. Skenario Demo Paling Aman

Skenario yang saya sarankan:

- Admin: akun admin pertama
- User: akun role `user`
- Item 1: buku utama qty 1
- Item 2: buku kedua qty 1

Kenapa skenario ini aman:

- totalnya mudah dijelaskan
- keranjang terlihat lebih realistis
- vendor order nanti menampilkan lebih dari satu item
- QRIS-only membuat alur presentasi lebih singkat

## 11. Verifikasi yang Sudah Dijalankan

Test feature yang sudah lulus:

```powershell
php vendor/bin/phpunit --testsuite Feature --do-not-cache-result
```

Yang tercakup:

- assignment lama tetap jalan
- checkout user Midtrans membuat order pending
- webhook Midtrans mengubah order menjadi lunas
- refresh status manual sinkron dengan status Midtrans

## 12. Checklist Sebelum Demo

Pastikan ini semua sudah siap:

1. `.env` sudah terisi key Midtrans sandbox.
2. `php artisan migrate` sudah sukses.
3. Jika perlu data cepat, `php artisan db:seed --class=DemoTokoSeeder` sudah dijalankan.
4. `php artisan serve` sedang berjalan.
5. Kalau ingin webhook otomatis dari Midtrans ke lokal, `ngrok http 8000` juga sedang berjalan.
6. Notification URL di dashboard Midtrans sudah mengarah ke endpoint Laravel.
