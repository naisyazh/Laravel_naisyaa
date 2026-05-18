@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-home"></i>
            </span> Dashboard
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Overview <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                </li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card bg-gradient-dark card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Selamat Datang, {{ Auth::user()->name }}! 
                        <i class="mdi mdi-bookmark-outline mdi-24px float-right"></i>
                    </h4>
                    <h2 class="mb-5">Anda login sebagai <span class="badge badge-outline-light">{{ ucfirst(Auth::user()->role) }}</span></h2>
                    <p class="card-text">Sistem Koleksi Buku & Manajemen Dokumen Eksklusif.</p>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->role == 'admin')
    <div class="row">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-danger card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Koleksi Buku <i class="mdi mdi-book-open-page-variant mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{ \App\Models\Buku::count() }} Buku</h2>
                    <h6 class="card-text">Tersebar di berbagai kategori</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-info card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total User Aktif <i class="mdi mdi-account-group mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{ \App\Models\User::count() }} Pengguna</h2>
                    <h6 class="card-text">Termasuk Admin & Member</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-success card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Dokumen Terunggah <i class="mdi mdi-file-document mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{ \DB::table('documents')->count() }} File</h2>
                    <h6 class="card-text">Sertifikat & Undangan digital</h6>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-md-6 stretch-card grid-margin">
            <a href="{{ route('toko-buku.index') }}" class="text-decoration-none w-100">
                <div class="card bg-gradient-primary card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Checkout Toko Buku <i class="mdi mdi-cart mdi-24px float-right"></i></h4>
                        <p>Masuk ke halaman POS user untuk demo checkout buku dengan Midtrans.</p>
                        <h6 class="card-text">Demo Payment Gateway</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 stretch-card grid-margin">
            <a href="{{ route('otp.undangan') }}" class="text-decoration-none w-100">
                <div class="card bg-gradient-warning card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Undangan Eksklusif <i class="mdi mdi-email-open mdi-24px float-right"></i></h4>
                        <p>Lihat detail acara khusus dan konfirmasi kehadiran Anda.</p>
                        <h6 class="card-text">Akses Undangan Spesial</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Aktivitas Terakhir</h4>
                    <p class="text-muted small">Waktu Server: {{ now()->format('d M Y H:i') }}</p>
                    <hr>
                    <p>Gunakan menu sidebar di sebelah kiri untuk mengelola data buku, kategori, atau mencetak tag harga UMKM.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
