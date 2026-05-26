@extends('layouts.app')

@section('title', 'Manajemen Kartu NFC')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="mdi mdi-card-account-details"></i> Manajemen Kartu NFC</h2>
            <p class="text-muted">Daftarkan kartu NFC mahasiswa ke sistem</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="mdi mdi-plus"></i> Daftarkan Kartu
        </button>
    </div>
</div>

<div id="alertBox"></div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableKartu">
                    @forelse($kartu as $k)
                    <tr id="row-{{ $k->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $k->serial_number }}</code></td>
                        <td><strong>{{ $k->nama_mahasiswa }}</strong></td>
                        <td>{{ $k->nim }}</td>
                        <td>{{ $k->program_studi ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $k->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $k->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger"
                                    onclick="hapusKartu({{ $k->id }}, '{{ $k->nama_mahasiswa }}')">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada kartu NFC terdaftar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kartu -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="mdi mdi-nfc-variant"></i> Daftarkan Kartu NFC</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    Untuk mendapatkan serial number kartu, gunakan halaman
                    <a href="{{ route('nfc.scanner') }}" target="_blank">Scanner NFC</a>
                    dan tap kartu — serial number akan muncul di log.
                </div>
                <form id="formTambah">
                    <div class="mb-3">
                        <label class="form-label">Serial Number Kartu NFC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="serialNumber"
                               placeholder="Contoh: 04:AB:CD:EF:12:34:56" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mahasiswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="namaMahasiswa"
                               placeholder="Nama lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIM <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nim"
                               placeholder="Nomor Induk Mahasiswa" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" class="form-control" id="programStudi"
                               placeholder="Contoh: Teknik Informatika">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanKartu()" id="btnSimpan">
                    <i class="mdi mdi-content-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function simpanKartu() {
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...';

    try {
        const res = await fetch('{{ route('nfc.kartu.simpan') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                serial_number:  document.getElementById('serialNumber').value,
                nama_mahasiswa: document.getElementById('namaMahasiswa').value,
                nim:            document.getElementById('nim').value,
                program_studi:  document.getElementById('programStudi').value,
            })
        });

        const data = await res.json();

        if (data.success) {
            tampilkanAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalTambah')).hide();
            document.getElementById('formTambah').reset();

            // Tambah baris baru ke tabel
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            const tbody = document.getElementById('tableKartu');
            const rowCount = tbody.querySelectorAll('tr').length + 1;
            tbody.insertAdjacentHTML('afterbegin', `
                <tr id="row-${data.data.id}">
                    <td>${rowCount}</td>
                    <td><code>${data.data.serial_number}</code></td>
                    <td><strong>${data.data.nama_mahasiswa}</strong></td>
                    <td>${data.data.nim}</td>
                    <td>${data.data.program_studi ?? '-'}</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td>
                        <button class="btn btn-sm btn-danger"
                                onclick="hapusKartu(${data.data.id}, '${data.data.nama_mahasiswa}')">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </td>
                </tr>
            `);
        } else {
            tampilkanAlert('danger', data.message);
        }
    } catch (err) {
        tampilkanAlert('danger', 'Gagal: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-content-save"></i> Simpan';
    }
}

async function hapusKartu(id, nama) {
    if (!confirm(`Hapus kartu NFC milik "${nama}"?`)) return;

    try {
        const res = await fetch(`/nfc/kartu/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById(`row-${id}`).remove();
            tampilkanAlert('success', data.message);
        } else {
            tampilkanAlert('danger', data.message);
        }
    } catch (err) {
        tampilkanAlert('danger', 'Gagal: ' + err.message);
    }
}

function tampilkanAlert(type, message) {
    document.getElementById('alertBox').innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}
</script>
@endsection
