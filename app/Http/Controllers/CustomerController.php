<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $baseQuery = Customer::query();

        return view('customers.index', [
            'customers' => $baseQuery->with('creator')->latest()->get(),
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'blob' => (clone $baseQuery)->where('capture_mode', 'blob')->count(),
                'file' => (clone $baseQuery)->where('capture_mode', 'file')->count(),
            ],
        ]);
    }

    public function createBlob(): View
    {
        return $this->createView(
            title: 'Tambah Customer 1',
            heading: 'Simpan Foto Customer sebagai Blob Database',
            description: 'Mode ini menyimpan hasil tangkapan kamera langsung ke database sebagai blob data.',
            formAction: route('customers.store.blob'),
            submitLabel: 'Simpan ke Database',
        );
    }

    public function createFile(): View
    {
        return $this->createView(
            title: 'Tambah Customer 2',
            heading: 'Simpan Foto Customer sebagai File Gambar',
            description: 'Mode ini menyimpan file gambar ke folder project lalu hanya menyimpan path file di database.',
            formAction: route('customers.store.file'),
            submitLabel: 'Simpan File & Path',
        );
    }

    public function storeBlob(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        [$binary, $mime] = $this->decodeCapturedPhoto($validated['captured_photo']);
        $kodeCustomer = Customer::generateCode();
        $attributes = [
            'kode_customer' => $kodeCustomer,
            'nama' => $validated['nama'],
            'email' => $validated['email'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'capture_mode' => 'blob',
            'photo_blob_mime' => $mime,
            'created_by' => $request->user()->id,
        ];

        if (DB::getDriverName() === 'pgsql') {
            $timestamp = now();

            DB::statement(
                'insert into "customer" ("kode_customer", "nama", "email", "telepon", "alamat", "capture_mode", "photo_blob", "photo_blob_mime", "created_by", "updated_at", "created_at")
                values (?, ?, ?, ?, ?, ?, decode(?, \'base64\'), ?, ?, ?, ?)',
                [
                    $attributes['kode_customer'],
                    $attributes['nama'],
                    $attributes['email'],
                    $attributes['telepon'],
                    $attributes['alamat'],
                    $attributes['capture_mode'],
                    base64_encode($binary),
                    $attributes['photo_blob_mime'],
                    $attributes['created_by'],
                    $timestamp,
                    $timestamp,
                ]
            );
        } else {
            Customer::create([
                ...$attributes,
                'photo_blob' => $binary,
            ]);
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil disimpan ke database sebagai blob.');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        [$binary, $mime, $extension] = $this->decodeCapturedPhoto($validated['captured_photo'], true);
        $relativeDirectory = trim((string) config('customer.photo_upload_dir', 'uploads/customers'), '/\\');
        $absoluteDirectory = public_path($relativeDirectory);

        File::ensureDirectoryExists($absoluteDirectory);

        $filename = Str::uuid()->toString() . '.' . $extension;
        $relativePath = $relativeDirectory . '/' . $filename;

        file_put_contents(public_path($relativePath), $binary);

        Customer::create([
            'kode_customer' => Customer::generateCode(),
            'nama' => $validated['nama'],
            'email' => $validated['email'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'capture_mode' => 'file',
            'photo_path' => str_replace('\\', '/', $relativePath),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil disimpan sebagai file gambar.');
    }

    /**
     * @return array{nama:string,email?:string,telepon?:string,alamat?:string,captured_photo:string}
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string|max:255',
            'captured_photo' => 'required|string',
        ]);
    }

    /**
     * @return array{0:string,1:string,2?:string}
     */
    private function decodeCapturedPhoto(string $capturedPhoto, bool $withExtension = false): array
    {
        if (! preg_match('/^data:(image\/(?:png|jpeg));base64,(.+)$/', $capturedPhoto, $matches)) {
            throw ValidationException::withMessages([
                'captured_photo' => 'Format foto tidak valid. Ambil foto dari kamera terlebih dahulu.',
            ]);
        }

        $binary = base64_decode(str_replace(' ', '+', $matches[2]), true);

        if ($binary === false) {
            throw ValidationException::withMessages([
                'captured_photo' => 'Foto hasil kamera tidak bisa diproses.',
            ]);
        }

        $mime = $matches[1];

        if (! $withExtension) {
            return [$binary, $mime];
        }

        $extension = $mime === 'image/png' ? 'png' : 'jpg';

        return [$binary, $mime, $extension];
    }

    private function createView(
        string $title,
        string $heading,
        string $description,
        string $formAction,
        string $submitLabel,
    ): View {
        return view('customers.create', compact(
            'title',
            'heading',
            'description',
            'formAction',
            'submitLabel',
        ));
    }
}
