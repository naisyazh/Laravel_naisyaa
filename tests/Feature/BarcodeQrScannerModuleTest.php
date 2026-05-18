<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeQrScannerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_barcode_and_vendor_scanner_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('barang.scanner'))
            ->assertOk()
            ->assertSee('Scan Barcode Barang');

        $this->actingAs($admin)
            ->get(route('vendor.orders.scanner'))
            ->assertOk()
            ->assertSee('Scan QR Pesanan Customer');
    }

    public function test_admin_can_fetch_barang_details_from_scanned_barcode(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Barang::query()->create([
            'id_barang' => 'BRG00001',
            'nama' => 'Nasi Goreng',
            'harga' => 18000,
            'vendor_id' => $admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->getJson(route('barang.scan.show', 'BRG00001'))
            ->assertOk()
            ->assertJsonPath('data.id_barang', 'BRG00001')
            ->assertJsonPath('data.nama_barang', 'Nasi Goreng')
            ->assertJsonPath('data.harga_barang', 18000);
    }

    public function test_paid_order_page_is_accessible_via_pesanan_route_for_customer(): void
    {
        $customer = User::factory()->create([
            'role' => 'user',
        ]);

        $vendor = User::factory()->create([
            'role' => 'admin',
        ]);

        $order = Penjualan::query()->create([
            'nomor_transaksi' => 'TRX-QR-0001',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'total' => 36000,
            'payment_status' => 'paid',
            'payment_type' => 'qris',
            'status_message' => 'Pembayaran berhasil diverifikasi.',
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'barang_id' => 'BRG00001',
            'nama_barang' => 'Mie Ayam',
            'harga' => 18000,
            'jumlah' => 2,
            'subtotal' => 36000,
        ]);

        $this->actingAs($customer)
            ->get(route('pesanan.show', $order->nomor_transaksi))
            ->assertOk()
            ->assertSee('QR Pesanan')
            ->assertSee($order->nomor_transaksi)
            ->assertSee('Mie Ayam');
    }

    public function test_vendor_can_fetch_order_json_from_scanned_qr(): void
    {
        $customer = User::factory()->create([
            'role' => 'user',
        ]);

        $vendor = User::factory()->create([
            'role' => 'admin',
        ]);

        $order = Penjualan::query()->create([
            'nomor_transaksi' => 'TRX-QR-0002',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'total' => 25000,
            'payment_status' => 'paid',
            'payment_type' => 'qris',
            'status_message' => 'Lunas dan siap divalidasi vendor.',
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'barang_id' => 'BRG00002',
            'nama_barang' => 'Es Teh',
            'harga' => 5000,
            'jumlah' => 5,
            'subtotal' => 25000,
        ]);

        $this->actingAs($vendor)
            ->getJson(route('pesanan.show', $order->nomor_transaksi))
            ->assertOk()
            ->assertJsonPath('data.id_pesanan', 'TRX-QR-0002')
            ->assertJsonPath('data.status_pembayaran', 'paid')
            ->assertJsonCount(1, 'data.daftar_menu');

        $this->actingAs($vendor)
            ->getJson(route('vendor.orders.lookup', $order->nomor_transaksi))
            ->assertOk()
            ->assertJsonPath('data.id_pesanan', 'TRX-QR-0002')
            ->assertJsonPath('data.customer_name', $customer->name)
            ->assertJsonCount(1, 'data.daftar_menu');
    }
}
