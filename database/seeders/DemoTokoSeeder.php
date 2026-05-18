<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTokoSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->get();

        if ($vendors->isEmpty()) {
            $this->command?->warn('Tidak ada akun admin/vendor. Jalankan DatabaseSeeder atau buat akun admin terlebih dahulu.');
            return;
        }

        $demoMenus = [
            [
                'nama' => 'Atomic Habits',
                'harga' => 18000,
            ],
            [
                'nama' => 'Filosofi Teras',
                'harga' => 15000,
            ],
            [
                'nama' => 'Laut Bercerita',
                'harga' => 5000,
            ],
            [
                'nama' => 'Bicara Itu Ada Seninya',
                'harga' => 12000,
            ],
            [
                'nama' => 'Home Sweet Loan',
                'harga' => 10000,
            ],
            [
                'nama' => 'Keajaiban Toko Kelontong Namiya',
                'harga' => 20000,
            ],
        ];

        foreach ($vendors as $vendorIndex => $vendor) {
            foreach ($demoMenus as $menuIndex => $menu) {
                $kode = sprintf('DM%01d%05d', $vendorIndex + 1, $menuIndex + 1);

                Barang::query()->updateOrCreate(
                    ['id_barang' => $kode],
                    [
                        'nama' => $menu['nama'] . ' - ' . $vendor->name,
                        'harga' => $menu['harga'] + ($vendorIndex * 1000),
                        'vendor_id' => $vendor->id,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info('Demo buku toko berhasil disiapkan untuk admin.');
    }
}
