<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- 1. DATA USER (ADMIN) ---
        
        User::create([
            'name' => 'Abhi Svariyu (Admin)',
            'email' => 'abhizvariyu@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Naisya Zahra (Admin)',
            'email' => 'naisyaazaraa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Hazel MpR (Admin)',
            'email' => 'hazelmpr.id@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        
        // --- 2. DATA USER (PEMERAN USER BIASA) ---
        // Akun-akun ini yang akan menerima Sertifikat/Undangan dari Admin

        User::create([
            'name' => 'Abhi Sleeping',
            'email' => 'abhizsleeping@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Customer JualBeli',
            'email' => 'jualbeli7920@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'User Testing',
            'email' => 'user@mail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // --- 3. DATA MASTER (KATEGORI) ---
        
        $novel = Kategori::create(['nama_kategori' => 'Novel']);
        $biografi = Kategori::create(['nama_kategori' => 'Biografi']);
        $komik = Kategori::create(['nama_kategori' => 'Komik']);

        // --- 4. DATA BUKU ---
        
        Buku::create([
            'idkategori' => $novel->idkategori,
            'kode' => 'NV-01',
            'judul' => 'Home Sweet Loan',
            'pengarang' => 'Almira Bastari',
        ]);

        Buku::create([
            'idkategori' => $biografi->idkategori,
            'kode' => 'BO-01',
            'judul' => 'Mohammad Hatta, Untuk Negeriku',
            'pengarang' => 'Taufik Abdullah',
        ]);

        Buku::create([
            'idkategori' => $novel->idkategori,
            'kode' => 'NV-02',
            'judul' => 'Keajaiban Toko Kelontong Namiya',
            'pengarang' => 'Keigo Higashino',
        ]);
    }
}