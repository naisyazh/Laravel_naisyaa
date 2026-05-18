@php
    $prefix = $mode . '-pos';
@endphp

<div class="assignment-panel">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h5 class="mb-1">{{ $label }}</h5>
            <p class="text-muted mb-0 small">{{ $helper }}</p>
        </div>
        <span class="badge badge-light mt-2 mt-md-0">Lookup: <code>Enter</code> | Checkout: <code>POST</code></span>
    </div>

    <form id="{{ $prefix }}-form" novalidate>
        <div class="row g-3 align-items-end">
            <div class="col-xl-3 col-lg-4">
                <label class="form-label" for="{{ $prefix }}-kode">Kode Barang</label>
                <input type="text" id="{{ $prefix }}-kode" class="form-control" placeholder="Contoh: BRG00001"
                    autocomplete="off">
            </div>
            <div class="col-xl-4 col-lg-4">
                <label class="form-label" for="{{ $prefix }}-nama">Nama Barang</label>
                <input type="text" id="{{ $prefix }}-nama" class="form-control" readonly>
            </div>
            <div class="col-xl-3 col-lg-2">
                <label class="form-label" for="{{ $prefix }}-harga">Harga Barang</label>
                <input type="text" id="{{ $prefix }}-harga" class="form-control" readonly>
            </div>
            <div class="col-xl-2 col-lg-2">
                <label class="form-label" for="{{ $prefix }}-jumlah">Jumlah</label>
                <input type="number" id="{{ $prefix }}-jumlah" class="form-control" min="1" value="1">
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3 gap-3">
            <p class="small text-muted mb-0" id="{{ $prefix }}-lookup-status">
                Ketik kode barang lalu tekan Enter untuk mencari data.
            </p>
            <button type="submit" class="btn btn-gradient-primary assignment-action" id="{{ $prefix }}-add" disabled>
                Tambahkan
            </button>
        </div>
    </form>

    <div class="table-responsive mt-4">
        <table class="table table-hover align-middle assignment-table mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th style="width: 140px;">Jumlah</th>
                    <th>Subtotal</th>
                    <th style="width: 110px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="{{ $prefix }}-tbody">
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada item. Gunakan kode barang untuk mulai transaksi.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-4 align-items-center">
        <div class="col-lg-8">
            <p class="small text-muted mb-0" id="{{ $prefix }}-checkout-status">
                Perubahan jumlah dan hapus item akan langsung memperbarui total.
            </p>
        </div>
        <div class="col-lg-4">
            <div class="checkout-card">
                <div>
                    <span class="checkout-label">Total</span>
                    <strong id="{{ $prefix }}-total">Rp 0</strong>
                </div>
                <button type="button" class="btn btn-gradient-success assignment-action" id="{{ $prefix }}-pay" disabled>
                    Bayar
                </button>
            </div>
        </div>
    </div>
</div>
