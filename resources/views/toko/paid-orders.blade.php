@extends('layouts.app')

@section('title', 'Riwayat Pembayaran Lunas')

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-success text-white me-2">
                <i class="mdi mdi-check-decagram"></i>
            </span>
            Riwayat Pembayaran Lunas
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pembayaran Lunas</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Jumlah Transaksi Lunas</h4>
                    <h2 class="mb-0">{{ $paidStats['count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Total Nominal Lunas</h4>
                    <h2 class="mb-0">Rp {{ number_format($paidStats['total'], 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title mb-1">Transaksi yang Sudah Dibayar</h4>
            <p class="card-description mb-3">
                Data ditarik langsung dari database berdasarkan status <strong>Lunas</strong>, sehingga tidak hilang saat
                halaman di-refresh.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nomor Transaksi</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->nomor_transaksi }}</strong>
                                </td>
                                <td>
                                    {{ $order->items->map(fn($item) => $item->nama_barang . ' x' . $item->jumlah)->implode(', ') }}
                                </td>
                                <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td>{{ strtoupper($order->payment_type ?? '-') }}</td>
                                <td>
                                    <a href="{{ route('pesanan.show', $order->nomor_transaksi) }}"
                                        class="btn btn-sm btn-inverse-success">
                                        QR Pesanan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada transaksi berstatus lunas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
