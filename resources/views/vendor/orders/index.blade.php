@extends('layouts.app')

@section('title', 'Transaksi Toko Buku')

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-clipboard-text"></i>
            </span>
            Transaksi Toko Buku
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Transaksi Buku</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Pesanan Lunas</h4>
                    <h2 class="mb-0">{{ $stats['paid'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Menunggu Bayar</h4>
                    <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-gradient-danger text-white">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Gagal / Batal</h4>
                    <h2 class="mb-0">{{ $stats['failed'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <h4 class="font-weight-normal mb-2">Omzet Lunas</h4>
                    <h2 class="mb-0">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="card-title mb-1">Daftar Checkout User</h4>
                    <p class="card-description mb-0">Admin dapat memantau transaksi buku yang sudah terhubung dengan master buku toko.</p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('vendor.orders.scanner') }}" class="btn btn-gradient-info">
                        Scan QR Pesanan
                    </a>
                    <form method="GET" class="d-flex gap-2">
                        <select name="status" class="form-select">
                            <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Lunas</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $statusFilter === 'processing' ? 'selected' : '' }}>Diproses</option>
                            <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Gagal</option>
                            <option value="expired" {{ $statusFilter === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            <option value="refunded" {{ $statusFilter === 'refunded' ? 'selected' : '' }}>Refund</option>
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                        <button type="submit" class="btn btn-gradient-primary">Filter</button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Transaksi</th>
                            <th>Customer</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->nomor_transaksi }}</strong>
                                    <div class="text-muted small">{{ $order->created_at->format('d M Y H:i') }}</div>
                                </td>
                                <td>
                                    <strong>{{ $order->customer_name ?? $order->user?->name ?? '-' }}</strong>
                                    <div class="text-muted small">{{ $order->customer_phone ?? '-' }}</div>
                                </td>
                                <td>
                                    {{ $order->items->map(fn ($item) => $item->nama_barang . ' x' . $item->jumlah)->implode(', ') }}
                                </td>
                                <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $order->paymentStatusBadgeClass() }}">
                                        {{ $order->paymentStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ strtoupper($order->payment_type ?? '-') }}</strong>
                                    <div class="text-muted small">{{ optional($order->paid_at)->format('d M Y H:i') ?? '-' }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('vendor.orders.show', $order->nomor_transaksi) }}"
                                        class="btn btn-sm btn-inverse-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada pesanan untuk filter ini.
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
