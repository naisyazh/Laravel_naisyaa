@extends('layouts.app')

@section('title', 'Modul AJAX, jQuery, dan Axios')

@section('content')
    <div class="page-header mb-4">
        <h3 class="page-title d-flex align-items-center gap-2">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-layers-triple"></i>
            </span>
            Modul AJAX, jQuery &amp; Axios
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('otp.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Modul AJAX &amp; Axios</li>
            </ul>
        </nav>
    </div>

    @include('partials.jsq-assignment')
@endsection

@include('partials.jsq-assignment-assets')
