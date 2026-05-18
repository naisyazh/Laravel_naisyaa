

<div class="row mb-4">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Studi Kasus 1</h4>
                <p class="card-description mb-0">
                    Cascading <code>select</code> provinsi, kota, kecamatan, dan kelurahan dengan 2 versi request:
                    <strong>jQuery AJAX</strong> dan <strong>Axios</strong>.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Studi Kasus 2</h4>
                <p class="card-description mb-0">
                    Halaman POS yang mencari barang berdasarkan kode saat tombol <kbd>Enter</kbd> ditekan, mengelola
                    keranjang belanja, lalu menyimpan transaksi ke database lewat AJAX maupun Axios.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                    <div>
                        <h4 class="card-title mb-1">Cascading Wilayah Administrasi</h4>
                        <p class="card-description mb-0">Event yang dipakai untuk memicu request adalah <code>change</code> pada setiap select.</p>
                    </div>
                    <span class="badge badge-gradient-info mt-2 mt-lg-0">Source: provinsi -> kota -> kecamatan -> kelurahan</span>
                </div>

                <ul class="nav nav-pills assignment-tabs" id="region-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="region-ajax-tab" data-bs-toggle="pill"
                            data-bs-target="#region-ajax-panel" type="button" role="tab">
                            jQuery AJAX
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="region-axios-tab" data-bs-toggle="pill"
                            data-bs-target="#region-axios-panel" type="button" role="tab">
                            Axios
                        </button>
                    </li>
                </ul>

                <div class="tab-content border-0 px-0 pb-0 pt-3">
                    <div class="tab-pane fade show active" id="region-ajax-panel" role="tabpanel">
                        @include('partials.assignment-region-panel', [
                            'mode' => 'ajax',
                            'label' => 'Versi jQuery AJAX',
                            'helper' => 'Menggunakan $.ajax ke endpoint Laravel lokal.',
                        ])
                    </div>
                    <div class="tab-pane fade" id="region-axios-panel" role="tabpanel">
                        @include('partials.assignment-region-panel', [
                            'mode' => 'axios',
                            'label' => 'Versi Axios',
                            'helper' => 'Menggunakan axios.get ke endpoint Laravel lokal.',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                    <div>
                        <h4 class="card-title mb-1">Point Of Sales (POS)</h4>
                        <p class="card-description mb-0">
                            Ketik kode barang lalu tekan <code>Enter</code>. Nama dan harga akan terisi otomatis, jumlah default 1,
                            dan transaksi disimpan ke database saat tombol <strong>Bayar</strong> ditekan.
                        </p>
                    </div>
                    <span class="badge badge-gradient-warning mt-2 mt-lg-0">Duplikasi kode otomatis menambah jumlah</span>
                </div>

                <ul class="nav nav-pills assignment-tabs" id="pos-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="pos-ajax-tab" data-bs-toggle="pill"
                            data-bs-target="#pos-ajax-panel" type="button" role="tab">
                            jQuery AJAX
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="pos-axios-tab" data-bs-toggle="pill"
                            data-bs-target="#pos-axios-panel" type="button" role="tab">
                            Axios
                        </button>
                    </li>
                </ul>

                <div class="tab-content border-0 px-0 pb-0 pt-3">
                    <div class="tab-pane fade show active" id="pos-ajax-panel" role="tabpanel">
                        @include('partials.assignment-pos-panel', [
                            'mode' => 'ajax',
                            'label' => 'POS dengan jQuery AJAX',
                            'helper' => 'Lookup barang via $.ajax, checkout via POST AJAX ke Laravel.',
                        ])
                    </div>
                    <div class="tab-pane fade" id="pos-axios-panel" role="tabpanel">
                        @include('partials.assignment-pos-panel', [
                            'mode' => 'axios',
                            'label' => 'POS dengan Axios',
                            'helper' => 'Lookup barang via axios.get, checkout via axios.post ke Laravel.',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
