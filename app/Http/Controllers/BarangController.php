<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Services\Code39BarcodeService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    public function __construct(
        private readonly Code39BarcodeService $barcodeService,
    ) {
    }

    public function index(Request $request)
    {
        $barangs = Barang::query()
            ->where('vendor_id', $request->user()->id)
            ->orderBy('nama')
            ->get();

        return view('barang.index', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:50',
            'harga' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        Barang::create([
            'id_barang' => $this->generateBarangId(),
            'nama' => $request->nama,
            'harga' => $request->harga,
            'vendor_id' => $request->user()->id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $barang = Barang::query()
            ->where('id_barang', $id)
            ->where('vendor_id', request()->user()->id)
            ->firstOrFail();

        return response()->json($barang);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:50',
            'harga' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $barang = Barang::query()
            ->where('id_barang', $id)
            ->where('vendor_id', $request->user()->id)
            ->firstOrFail();

        $barang->update([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'Data barang berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $barang = Barang::query()
            ->where('id_barang', $id)
            ->where('vendor_id', request()->user()->id)
            ->firstOrFail();

        $barang->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus!');
    }

    public function cetakLabel(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
        ]);

        $barangs = Barang::query()
            ->where('vendor_id', $request->user()->id)
            ->whereIn('id_barang', $request->selected_ids)
            ->get();

        if ($barangs->isEmpty()) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $barangs = $barangs->map(function (Barang $barang) {
            $barcodeDisplayValue = strtoupper(trim($barang->id_barang));
            $barcodePayload = preg_match('/^BRG(\d{5})$/', $barcodeDisplayValue, $matches)
                ? $matches[1]
                : $barcodeDisplayValue;

            $barang->setAttribute('barcode_payload', $barcodePayload);
            $barang->setAttribute('barcode_display_value', $barcodeDisplayValue);
            $barang->setAttribute('barcode_data_uri', $this->barcodeService->toDataUri($barcodePayload, barHeight: 96));

            return $barang;
        });

        $skip = (($request->y - 1) * 5) + ($request->x - 1);

        $pdf = Pdf::loadView('barang.cetak_pdf', [
            'barangs' => $barangs,
            'skip' => $skip,
        ]);
        

        return $pdf->setPaper('a4', 'portrait')
            ->stream('label-harga.pdf');
    }

    private function generateBarangId(): string
    {
        $lastNumber = Barang::query()
            ->where('id_barang', 'like', 'BRG%')
            ->pluck('id_barang')
            ->map(function (string $idBarang) {
                return (int) preg_replace('/\D/', '', $idBarang);
            })
            ->max() ?? 0;

        do {
            $lastNumber++;
            $candidate = 'BRG' . str_pad((string) $lastNumber, 5, '0', STR_PAD_LEFT);
        } while (Barang::query()->where('id_barang', $candidate)->exists());

        return $candidate;
    }
}
