@extends('layouts.app')

@section('title', 'Master Data Toko')

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-store"></i>
            </span>
            Master Data Toko
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Master Data Toko</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">List Toko</h4>
                        <a href="{{ route('toko.create') }}" class="btn btn-gradient-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Toko
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Nama Toko</th>
                                    <th>Alamat</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Accuracy (m)</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tokos as $toko)
                                    <tr>
                                        <td>
                                            <strong>{{ $toko->barcode }}</strong>
                                        </td>
                                        <td>{{ $toko->nama_toko }}</td>
                                        <td>{{ Str::limit($toko->alamat, 50) }}</td>
                                        <td>{{ $toko->latitude }}</td>
                                        <td>{{ $toko->longitude }}</td>
                                        <td>{{ $toko->accuracy }}</td>
                                        <td>
                                            @if($toko->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('toko.cetak-barcode') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="toko_ids[]" value="{{ $toko->id }}">
                                                <button type="submit" class="btn btn-sm btn-gradient-info">
                                                    <i class="mdi mdi-barcode"></i> Cetak
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <p class="text-muted mb-0">Belum ada data toko.</p>
                                            <a href="{{ route('toko.create') }}" class="btn btn-sm btn-gradient-primary mt-2">
                                                <i class="mdi mdi-plus"></i> Tambah Toko Pertama
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($tokos->hasPages())
                        <div class="mt-3">
                            {{ $tokos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
