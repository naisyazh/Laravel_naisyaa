@extends('layouts.app')

@section('title', 'Sertifikat Partisipasi')

@section('content')
<style>
    @media print {
        nav, .navbar, .sidebar, .footer, .no-print { display: none !important; }
        .content-wrapper { background: white !important; padding: 0 !important; }
        .main-panel { width: 100% !important; }
        .card { border: none !important; box-shadow: none !important; }
        .print-container { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        img { width: 100% !important; border-radius: 0 !important; }
    }
    .cert-group:hover .cert-overlay { opacity: 1; }
    .cert-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(182, 109, 255, 0.1);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: 0.3s; border-radius: 15px;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10 grid-margin stretch-card">
        <div class="card shadow-sm print-container">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h4 class="card-title mb-0 text-uppercase tracking-wider">Sertifikat Digital</h4>
                    <a href="{{ route('otp.dashboard') }}" class="btn btn-sm btn-light font-weight-bold">
                        <i class="mdi mdi-arrow-left"></i> Dashboard
                    </a>
                </div>

                @if($document)
                    <div class="position-relative cert-group">
                        <img src="{{ asset('storage/' . $document->file_path) }}" 
                             alt="Sertifikat" 
                             class="w-100 rounded border shadow-sm">
                        
                        <div class="cert-overlay no-print">
                            <button onclick="window.open('{{ asset('storage/' . $document->file_path) }}', '_blank')" class="btn btn-light btn-rounded font-weight-bold shadow">
                                Lihat Gambar Penuh
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 text-center no-print">
                        <div class="btn-group shadow-sm">
                            <button onclick="window.print()" class="btn btn-gradient-primary btn-lg font-weight-bold px-5">
                                <i class="mdi mdi-printer mr-2"></i> Simpan / Cetak PDF
                            </button>
                            <a href="{{ asset('storage/' . $document->file_path) }}" download="Sertifikat_{{ Auth::user()->name }}.png" class="btn btn-outline-secondary btn-lg font-weight-bold px-4">
                                Unduh Gambar
                            </a>
                        </div>
                        <p class="mt-4 text-muted small font-italic">
                            Sertifikat ini diterbitkan secara resmi untuk <strong>{{ Auth::user()->name }}</strong>
                        </p>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="mdi mdi-alert-circle-outline text-warning display-1"></i>
                        <h3 class="mt-3">Sertifikat Belum Tersedia</h3>
                        <p class="text-muted">Admin belum mengunggah sertifikat untuk akun Anda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection