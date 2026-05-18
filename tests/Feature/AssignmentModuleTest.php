<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_assignment_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('assignment'));

        $response
            ->assertOk()
            ->assertSee('Modul AJAX, jQuery &amp; Axios', false)
            ->assertSee('Point Of Sales (POS)', false);
    }

    public function test_authenticated_user_can_fetch_provinces(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('assignment.regions.provinces'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    ['id', 'name'],
                ],
            ]);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_lookup_barang_returns_barang_payload(): void
    {
        $user = User::factory()->create();
        $barang = Barang::create([
            'id_barang' => 'BRG00001',
            'nama' => 'Kopi Susu',
            'harga' => 18000,
        ]);

        $response = $this->actingAs($user)->getJson(route('assignment.barang.lookup', [
            'kode' => $barang->id_barang,
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.kode', 'BRG00001')
            ->assertJsonPath('data.nama', 'Kopi Susu')
            ->assertJsonPath('data.harga', 18000);
    }

    public function test_checkout_creates_penjualan_and_items(): void
    {
        $user = User::factory()->create();

        Barang::create([
            'id_barang' => 'BRG00001',
            'nama' => 'Kopi Susu',
            'harga' => 18000,
        ]);

        Barang::create([
            'id_barang' => 'BRG00002',
            'nama' => 'Roti Bakar',
            'harga' => 12000,
        ]);

        $response = $this->actingAs($user)->postJson(route('assignment.checkout'), [
            'items' => [
                ['kode' => 'BRG00001', 'jumlah' => 2],
                ['kode' => 'BRG00002', 'jumlah' => 1],
                ['kode' => 'BRG00001', 'jumlah' => 1],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total', 66000);

        $this->assertDatabaseCount('penjualans', 1);
        $this->assertDatabaseCount('penjualan_items', 2);

        $penjualan = Penjualan::query()->with('items')->firstOrFail();

        $this->assertSame(66000, (int) $penjualan->total);
        $this->assertCount(2, $penjualan->items);
        $this->assertDatabaseHas('penjualan_items', [
            'penjualan_id' => $penjualan->id,
            'barang_id' => 'BRG00001',
            'jumlah' => 3,
            'subtotal' => 54000,
        ]);
    }
}
