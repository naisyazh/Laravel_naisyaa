@extends('layouts.app')

@section('title', 'Riwayat Kunjungan Toko')

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-history"></i>
            </span>
            Riwayat Kunjungan Toko
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kunjungan-toko.index') }}">Kunjungan Toko</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Riwayat Kunjungan</h4>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Toko</th>
                                    <th>Sales</th>
                                    <th>Jarak (m)</th>
                                    <th>Threshold (m)</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kunjungans as $kunjungan)
                                    <tr>
                                        <td>{{ $kunjungan->waktu_kunjungan->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $kunjungan->toko->nama_toko }}</strong><br>
                                            <small class="text-muted">{{ $kunjungan->toko->barcode }}</small>
                                        </td>
                                        <td>{{ $kunjungan->sales->name }}</td>
                                        <td>{{ number_format($kunjungan->jarak_meter, 2) }}</td>
                                        <td>
                                            {{ number_format($kunjungan->threshold_meter + $kunjungan->toko_accuracy + $kunjungan->sales_accuracy, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $kunjungan->getStatusBadgeClass() }}">
                                                {{ $kunjungan->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-gradient-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailModal{{ $kunjungan->id }}">
                                                <i class="mdi mdi-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal{{ $kunjungan->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Kunjungan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Data Toko</h6>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <td>Nama</td>
                                                                    <td><strong>{{ $kunjungan->toko->nama_toko }}</strong></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Barcode</td>
                                                                    <td>{{ $kunjungan->toko->barcode }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Latitude</td>
                                                                    <td>{{ $kunjungan->toko_latitude }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Longitude</td>
                                                                    <td>{{ $kunjungan->toko_longitude }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Accuracy</td>
                                                                    <td>{{ $kunjungan->toko_accuracy }} m</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Data Sales</h6>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <td>Nama</td>
                                                                    <td><strong>{{ $kunjungan->sales->name }}</strong></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Email</td>
                                                                    <td>{{ $kunjungan->sales->email }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Latitude</td>
                                                                    <td>{{ $kunjungan->sales_latitude }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Longitude</td>
                                                                    <td>{{ $kunjungan->sales_longitude }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Accuracy</td>
                                                                    <td>{{ $kunjungan->sales_accuracy }} m</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <h6>Hasil Validasi</h6>
                                                    <div class="alert {{ $kunjungan->isDiterima() ? 'alert-success' : 'alert-danger' }}">
                                                        <h5>{{ $kunjungan->getStatusLabel() }}</h5>
                                                        <p class="mb-0">{{ $kunjungan->keterangan }}</p>
                                                    </div>

                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Jarak Aktual</td>
                                                            <td><strong>{{ number_format($kunjungan->jarak_meter, 2) }} m</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Threshold Base</td>
                                                            <td>{{ number_format($kunjungan->threshold_meter, 2) }} m</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Threshold Efektif</td>
                                                            <td>
                                                                <strong>{{ number_format($kunjungan->threshold_meter + $kunjungan->toko_accuracy + $kunjungan->sales_accuracy, 2) }} m</strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    ({{ $kunjungan->threshold_meter }} + {{ $kunjungan->toko_accuracy }} + {{ $kunjungan->sales_accuracy }})
                                                                </small>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Waktu Kunjungan</td>
                                                            <td>{{ $kunjungan->waktu_kunjungan->format('d F Y, H:i:s') }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted mb-0">Belum ada riwayat kunjungan.</p>
                                            <a href="{{ route('kunjungan-toko.index') }}" class="btn btn-sm btn-gradient-primary mt-2">
                                                <i class="mdi mdi-map-marker-check"></i> Mulai Kunjungan
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($kunjungans->hasPages())
                        <div class="mt-3">
                            {{ $kunjungans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
