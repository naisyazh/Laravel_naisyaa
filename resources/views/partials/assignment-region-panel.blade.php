@php
    $prefix = $mode . '-region';
@endphp

<div class="assignment-panel">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h5 class="mb-1">{{ $label }}</h5>
            <p class="text-muted mb-0 small">{{ $helper }}</p>
        </div>
        <span class="badge badge-light mt-2 mt-md-0">Trigger event: <code>change</code></span>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="{{ $prefix }}-province">Provinsi</label>
                    <select id="{{ $prefix }}-province" class="form-select assignment-select">
                        <option value="">Memuat provinsi...</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="{{ $prefix }}-regency">Kota / Kabupaten</label>
                    <select id="{{ $prefix }}-regency" class="form-select assignment-select" disabled>
                        <option value="">Pilih Kota / Kabupaten</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="{{ $prefix }}-district">Kecamatan</label>
                    <select id="{{ $prefix }}-district" class="form-select assignment-select" disabled>
                        <option value="">Pilih Kecamatan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="{{ $prefix }}-village">Kelurahan</label>
                    <select id="{{ $prefix }}-village" class="form-select assignment-select" disabled>
                        <option value="">Pilih Kelurahan</option>
                    </select>
                </div>
            </div>
            <p class="small text-muted mt-3 mb-0" id="{{ $prefix }}-status">Menyiapkan data wilayah...</p>
        </div>

        <div class="col-lg-4">
            <div class="summary-card h-100">
                <h6 class="mb-3">Ringkasan Pilihan</h6>
                <div class="summary-item">
                    <span>Provinsi</span>
                    <strong id="{{ $prefix }}-province-output">-</strong>
                </div>
                <div class="summary-item">
                    <span>Kota / Kabupaten</span>
                    <strong id="{{ $prefix }}-regency-output">-</strong>
                </div>
                <div class="summary-item">
                    <span>Kecamatan</span>
                    <strong id="{{ $prefix }}-district-output">-</strong>
                </div>
                <div class="summary-item border-0 pb-0 mb-0">
                    <span>Kelurahan</span>
                    <strong id="{{ $prefix }}-village-output">-</strong>
                </div>
            </div>
        </div>
    </div>
</div>
