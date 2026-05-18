@extends('layouts.app')

@section('title', 'Admin - Kelola Antrian')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="mdi mdi-view-dashboard"></i> Dashboard Kelola Antrian</h2>
            <p class="text-muted">Kelola antrian secara real-time</p>
        </div>
    </div>

    <!-- Status Connection -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info" role="alert" id="connectionStatus">
                <i class="mdi mdi-loading mdi-spin"></i> Menghubungkan ke server...
            </div>
        </div>
    </div>

    <!-- Antrian Dipanggil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="mdi mdi-account-voice"></i> Sedang Dipanggil</h4>
                </div>
                <div class="card-body text-center" id="dipanggilContainer">
                    @if($dipanggil)
                        <div class="display-1 text-success fw-bold">{{ str_pad($dipanggil->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</div>
                        <h3 class="mt-3">{{ $dipanggil->nama }}</h3>
                        @if($dipanggil->ruangan)
                            <p class="text-muted">Ruangan: {{ $dipanggil->ruangan }}</p>
                        @endif
                        <small class="text-muted">Dipanggil: {{ $dipanggil->waktu_dipanggil->format('H:i:s') }}</small>
                    @else
                        <p class="text-muted mb-0">Belum ada antrian yang dipanggil</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Antrian Menunggu -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="mdi mdi-clock-outline"></i> Antrian Menunggu</h4>
                    <span class="badge bg-dark" id="countMenunggu">{{ $menunggu->count() }}</span>
                </div>
                <div class="card-body">
                    <!-- Form Panggil -->
                    <form id="formPanggil" class="mb-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-7">
                                <input type="number" class="form-control" id="ruangan" name="ruangan"
                                       placeholder="Nomor Ruangan (opsional)" min="1">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100" id="btnPanggil">
                                    <i class="mdi mdi-phone-forward"></i> Panggil Berikutnya
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100" onclick="resetAntrian()" title="Reset semua data antrian">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- List Antrian Menunggu -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="100">Nomor</th>
                                    <th>Nama</th>
                                    <th width="120">Waktu Daftar</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableMenunggu">
                                @forelse($menunggu as $antrian)
                                    <tr data-id="{{ $antrian->id }}">
                                        <td><span class="badge bg-warning text-dark fs-6">{{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</span></td>
                                        <td><strong>{{ $antrian->nama }}</strong></td>
                                        <td><small>{{ $antrian->waktu_daftar->format('H:i:s') }}</small></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="tandaiTerlewat({{ $antrian->id }})">
                                                <i class="mdi mdi-close"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Tidak ada antrian menunggu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Antrian Terlewat -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="mdi mdi-alert-circle"></i> Terlewat</h5>
                    <span class="badge bg-dark" id="countTerlewat">{{ $terlewat->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="list-group" id="listTerlewat">
                        @forelse($terlewat as $antrian)
                            <div class="list-group-item list-group-item-action"
                                 data-id="{{ $antrian->id }}"
                                 ondblclick="panggilUlang({{ $antrian->id }})"
                                 style="cursor: pointer;"
                                 title="Double-click untuk panggil ulang">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-danger">{{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</span>
                                        <strong class="ms-2">{{ $antrian->nama }}</strong>
                                    </div>
                                    <button class="btn btn-sm btn-outline-success" onclick="panggilUlang({{ $antrian->id }}); event.stopPropagation();">
                                        <i class="mdi mdi-phone"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ $antrian->waktu_daftar->format('H:i:s') }}</small>
                            </div>
                        @empty
                            <p class="text-center text-muted mb-0">Tidak ada antrian terlewat</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let pollingInterval = null;

function startPolling() {
    fetchData();
    pollingInterval = setInterval(fetchData, 2000);
}

async function fetchData() {
    try {
        const response = await fetch('{{ route('antrian.poll') }}');
        if (!response.ok) throw new Error('Server error');
        const data = await response.json();
        updateUI(data);

        const status = document.getElementById('connectionStatus');
        status.innerHTML = `<i class="mdi mdi-check-circle"></i> Terhubung ke server (Real-time aktif)`;
        status.className = 'alert alert-success';
    } catch (error) {
        const status = document.getElementById('connectionStatus');
        status.innerHTML = `<i class="mdi mdi-alert-circle"></i> Koneksi terputus. Mencoba reconnect...`;
        status.className = 'alert alert-danger';
    }
}

function updateUI(data) {
    const dipanggilContainer = document.getElementById('dipanggilContainer');
    if (data.dipanggil) {
        dipanggilContainer.innerHTML = `
            <div class="display-1 text-success fw-bold">${String(data.dipanggil.nomor_antrian).padStart(3, '0')}</div>
            <h3 class="mt-3">${data.dipanggil.nama}</h3>
            ${data.dipanggil.ruangan ? `<p class="text-muted">Ruangan: ${data.dipanggil.ruangan}</p>` : ''}
            <small class="text-muted">Dipanggil: ${data.dipanggil.waktu_dipanggil}</small>
        `;
    } else {
        dipanggilContainer.innerHTML = '<p class="text-muted mb-0">Belum ada antrian yang dipanggil</p>';
    }

    const tableMenunggu = document.getElementById('tableMenunggu');
    document.getElementById('countMenunggu').textContent = data.menunggu.length;
    tableMenunggu.innerHTML = data.menunggu.length > 0
        ? data.menunggu.map(a => `
            <tr data-id="${a.id}">
                <td><span class="badge bg-warning text-dark fs-6">${String(a.nomor_antrian).padStart(3, '0')}</span></td>
                <td><strong>${a.nama}</strong></td>
                <td><small>${a.waktu_daftar}</small></td>
                <td><button class="btn btn-sm btn-danger" onclick="tandaiTerlewat(${a.id})"><i class="mdi mdi-close"></i></button></td>
            </tr>`).join('')
        : '<tr><td colspan="4" class="text-center text-muted">Tidak ada antrian menunggu</td></tr>';

    const listTerlewat = document.getElementById('listTerlewat');
    document.getElementById('countTerlewat').textContent = data.terlewat.length;
    listTerlewat.innerHTML = data.terlewat.length > 0
        ? data.terlewat.map(a => `
            <div class="list-group-item list-group-item-action" data-id="${a.id}"
                 ondblclick="panggilUlang(${a.id})" style="cursor:pointer" title="Double-click untuk panggil ulang">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-danger">${String(a.nomor_antrian).padStart(3, '0')}</span>
                        <strong class="ms-2">${a.nama}</strong>
                    </div>
                    <button class="btn btn-sm btn-outline-success" onclick="panggilUlang(${a.id}); event.stopPropagation();">
                        <i class="mdi mdi-phone"></i>
                    </button>
                </div>
                <small class="text-muted">${a.waktu_daftar}</small>
            </div>`).join('')
        : '<p class="text-center text-muted mb-0">Tidak ada antrian terlewat</p>';
}

document.getElementById('formPanggil').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btnPanggil = document.getElementById('btnPanggil');
    const ruangan = document.getElementById('ruangan').value;
    btnPanggil.disabled = true;
    btnPanggil.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Memanggil...';
    try {
        const res = await fetch('{{ route('antrian.panggil') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ruangan: ruangan || null })
        });
        const result = await res.json();
        if (result.success) { document.getElementById('formPanggil').reset(); showToast('success', result.message); fetchData(); }
        else showToast('error', result.message);
    } catch (e) { showToast('error', 'Terjadi kesalahan: ' + e.message); }
    finally { btnPanggil.disabled = false; btnPanggil.innerHTML = '<i class="mdi mdi-phone-forward"></i> Panggil Berikutnya'; }
});

async function resetAntrian() {
    if (!confirm('Reset semua data antrian? Data tidak bisa dikembalikan.')) return;
    try {
        const res = await fetch('{{ route('antrian.reset') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if (result.success) { showToast('success', result.message); fetchData(); }
        else showToast('error', result.message);
    } catch (e) { showToast('error', 'Terjadi kesalahan: ' + e.message); }
}

async function tandaiTerlewat(id) {
    if (!confirm('Tandai antrian ini sebagai terlewat?')) return;
    try {
        const res = await fetch(`/antrian/${id}/terlewat`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if (result.success) { showToast('success', result.message); fetchData(); }
        else showToast('error', result.message);
    } catch (e) { showToast('error', 'Terjadi kesalahan: ' + e.message); }
}

async function panggilUlang(id) {
    const ruangan = prompt('Masukkan nomor ruangan (opsional):');
    try {
        const res = await fetch(`/antrian/${id}/panggil-ulang`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ruangan: ruangan || null })
        });
        const result = await res.json();
        if (result.success) { showToast('success', result.message); fetchData(); }
        else showToast('error', result.message);
    } catch (e) { showToast('error', 'Terjadi kesalahan: ' + e.message); }
}

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `<i class="mdi mdi-${type === 'success' ? 'check-circle' : 'alert-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

startPolling();
window.addEventListener('beforeunload', () => { if (pollingInterval) clearInterval(pollingInterval); });
</script>
@endsection
