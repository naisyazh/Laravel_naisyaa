@extends('layouts.app')

@section('title', 'Undangan Eksklusif')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Playfair+Display:ital,wght@0,700;1,400&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    .font-pinyon { font-family: 'Pinyon Script', cursive; }
    .font-playfair { font-family: 'Playfair Display', serif; }
    .sakura {
        position: fixed; top: -10%; background: #ffd1dc;
        border-radius: 100% 0 100% 0; animation: fall linear infinite;
        z-index: 1; pointer-events: none;
    }
    @keyframes fall {
        0% { top: -10%; transform: translateX(0) rotate(0deg); opacity: 1; }
        100% { top: 100%; transform: translateX(100px) rotate(360deg); opacity: 0; }
    }
    .invitation-card {
        background: white;
        border-radius: 40px;
        border: 1px solid #fce4ec;
        position: relative;
        overflow: hidden;
    }
    .invitation-img {
        width: 100%;
        max-height: 700px;
        object-fit: contain;
        border-radius: 20px;
        margin-bottom: 10px;
    }
</style>

<div id="sakura-container"></div>

<div class="row justify-content-center">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card invitation-card shadow-lg">
            <div class="card-body p-5 text-center">
                <div class="d-flex justify-content-end mb-4">
                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px; border: 4px solid white !important;">
                        <span class="font-pinyon text-primary h3 mb-0 font-weight-bold">NZ</span>
                    </div>
                </div>

                <h4 class="text-uppercase tracking-widest text-primary small font-weight-bold mb-4" style="letter-spacing: 0.5em;">Special Invitation</h4>
                
                <p class="font-playfair font-italic text-muted mb-2">Diberikan Kepada,</p>
                <h1 class="font-pinyon display-3 text-dark mb-4">{{ Auth::user()->name }}</h1>

                @if($document)
                    <div class="px-3">
                        <img src="{{ asset('storage/' . $document->file_path) }}" class="invitation-img shadow-sm border">
                    </div>
                    
                    <div class="mt-4 no-print">
                         <a href="{{ asset('storage/' . $document->file_path) }}" download="Undangan_{{ Auth::user()->name }}.png" class="btn btn-gradient-primary btn-lg font-weight-bold">
                            <i class="mdi mdi-download mr-2"></i> UNDUH UNDANGAN
                        </a>
                    </div>
                @else
                    <div class="py-5">
                        <i class="mdi mdi-email-outline text-muted display-1"></i>
                        <p class="mt-3 text-muted">Belum ada undangan khusus untuk Anda saat ini.</p>
                    </div>
                @endif
            </div>
            <div class="p-1 bg-gradient-primary"></div>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('sakura-container');
    for (let i = 0; i < 15; i++) {
        const petal = document.createElement('div');
        petal.className = 'sakura';
        petal.style.left = Math.random() * 100 + 'vw';
        petal.style.width = Math.random() * 10 + 5 + 'px';
        petal.style.height = petal.style.width;
        petal.style.animationDuration = Math.random() * 5 + 5 + 's';
        petal.style.opacity = Math.random();
        container.appendChild(petal);
    }
</script>
@endsection