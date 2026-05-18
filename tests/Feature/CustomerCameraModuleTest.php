<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomerCameraModuleTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE_IMAGE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4////fwAJ+wP+Lh+Q6QAAAABJRU5ErkJggg==';

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/testing-customers'));

        parent::tearDown();
    }

    public function test_admin_can_open_customer_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Data Customer');

        $this->actingAs($admin)
            ->get(route('customers.create.blob'))
            ->assertOk()
            ->assertSee('Tambah Customer 1');

        $this->actingAs($admin)
            ->get(route('customers.create.file'))
            ->assertOk()
            ->assertSee('Tambah Customer 2');
    }

    public function test_admin_can_store_customer_photo_as_blob(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('customers.store.blob'), [
            'nama' => 'Customer Blob',
            'email' => 'blob@example.test',
            'telepon' => '08123456789',
            'alamat' => 'Jl. Blob Database',
            'captured_photo' => self::SAMPLE_IMAGE,
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::query()->firstOrFail();

        $this->assertSame('blob', $customer->capture_mode);
        $this->assertSame('image/png', $customer->photo_blob_mime);
        $this->assertNotNull($customer->blobDataUri());
        $this->assertDatabaseHas('customer', [
            'kode_customer' => 'CST00001',
            'nama' => 'Customer Blob',
            'capture_mode' => 'blob',
        ]);
    }

    public function test_admin_can_store_customer_photo_as_file_path(): void
    {
        config()->set('customer.photo_upload_dir', 'uploads/testing-customers');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('customers.store.file'), [
            'nama' => 'Customer File',
            'email' => 'file@example.test',
            'telepon' => '0899001122',
            'alamat' => 'Jl. File Path',
            'captured_photo' => self::SAMPLE_IMAGE,
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::query()->firstOrFail();

        $this->assertSame('file', $customer->capture_mode);
        $this->assertNotNull($customer->photo_path);
        $this->assertFileExists(public_path($customer->photo_path));
        $this->assertDatabaseHas('customer', [
            'kode_customer' => 'CST00001',
            'nama' => 'Customer File',
            'capture_mode' => 'file',
        ]);
    }
}
