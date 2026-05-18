@extends('layouts.app')

@section('title', 'Data Customer')

@section('style')
    <style>
        .customer-stat-card {
            border-radius: 1rem;
            color: #fff;
            overflow: hidden;
        }

        .customer-thumb {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 0.9rem;
            border: 1px solid #ebe7f8;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-account-box-multiple"></i>
            </span>
            Data Customer
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Customer</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-gradient-primary customer-stat-card">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Total Customer</h4>
                    <h2 class="mb-0">{{ $stats['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-gradient-info customer-stat-card">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Mode Blob Database</h4>
                    <h2 class="mb-0">{{ $stats['blob'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card bg-gradient-success customer-stat-card">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Mode File Path</h4>
                    <h2 class="mb-0">{{ $stats['file'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="card-title mb-1">Master Customer</h4>
                            <p class="text-muted mb-0">
                                Halaman ini menampilkan hasil penyimpanan foto customer dari kamera dalam dua mode berbeda.
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('customers.create.blob') }}" class="btn btn-gradient-primary">
                                Tambah Customer 1
                            </a>
                            <a href="{{ route('customers.create.file') }}" class="btn btn-gradient-info">
                                Tambah Customer 2
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Foto</th>
                                    <th>Nama Customer</th>
                                    <th>Kontak</th>
                                    <th>Metode Simpan</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                    @php($previewSource = $customer->previewSource())
                                    <tr>
                                        <td>
                                            <strong>{{ $customer->kode_customer }}</strong>
                                        </td>
                                        <td>
                                            @if ($previewSource)
                                                <img src="{{ $previewSource }}" alt="{{ $customer->nama }}" class="customer-thumb">
                                            @else
                                                <span class="badge badge-gradient-secondary">Belum Ada Foto</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $customer->nama }}</strong>
                                            @if ($customer->alamat)
                                                <div class="text-muted small">{{ $customer->alamat }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $customer->email ?: '-' }}</div>
                                            <div class="text-muted small">{{ $customer->telepon ?: '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $customer->capture_mode === 'blob' ? 'badge-gradient-primary' : 'badge-gradient-info' }}">
                                                {{ $customer->captureModeLabel() }}
                                            </span>
                                            @if ($customer->photo_path)
                                                <div class="text-muted small mt-1">{{ $customer->photo_path }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $customer->creator?->name ?? '-' }}</td>
                                        <td>{{ $customer->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Belum ada data customer. Gunakan menu Tambah Customer 1 atau Tambah Customer 2.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
