@extends('layouts.app')

@section('title', 'Riwayat Absensi NFC')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="mdi mdi-history"></i> Riwayat Absensi NFC</h2>
        <p class="text-muted">Log semua scan kartu NFC</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Mata Kuliah</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                    <tr>
                        <td>{{ $absensi->firstItem() + $loop->index }}</td>
                        <td>
                            <small class="text-muted">{{ $a->waktu_absen->format('d/m/Y') }}</small><br>
                            <strong>{{ $a->waktu_absen->format('H:i:s') }}</strong>
                        </td>
                        <td>{{ $a->kartu?->nama_mahasiswa ?? '<span class="text-muted">-</span>' }}</td>
                        <td>{{ $a->kartu?->nim ?? '-' }}</td>
                        <td>{{ $a->mata_kuliah }}</td>
                        <td><code>{{ $a->serial_number }}</code></td>
                        <td>
                            <span class="badge {{ $a->getStatusBadgeClass() }}">
                                {{ $a->getStatusLabel() }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada riwayat absensi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $absensi->links() }}
        </div>
    </div>
</div>
@endsection
