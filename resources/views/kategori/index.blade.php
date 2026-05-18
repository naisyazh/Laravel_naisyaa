@extends('layouts.app')

@section('title', 'Master Kategori')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Master Kategori</h4>
                <p class="card-description"> Daftar kategori buku perpustakaan </p>
                
                {{-- Tombol Tambah (Hanya Admin) --}}
                @if(Auth::user()->role == 'admin')
                <a href="{{ route('kategori.create') }}" class="btn btn-gradient-primary btn-fw mb-3">
                    <i class="mdi mdi-plus"></i> Tambah Kategori
                </a>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th> # </th>
                                <th> Nama Kategori </th>
                                {{-- Kolom Aksi (Hanya Admin) --}}
                                @if(Auth::user()->role == 'admin')
                                <th> Aksi </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kategori as $k)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $k->nama_kategori }} </td>
                                
                                {{-- Tombol Aksi (Hanya Admin) --}}
                                @if(Auth::user()->role == 'admin')
                                <td>
                                    <a href="{{ route('kategori.edit', $k->idkategori) }}" class="btn btn-inverse-warning btn-sm btn-icon-text">
                                        <i class="mdi mdi-pencil btn-icon-prepend"></i> Edit
                                    </a>
                                    
                                    <form action="{{ route('kategori.destroy', $k->idkategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-inverse-danger btn-sm btn-icon-text">
                                            <i class="mdi mdi-delete btn-icon-prepend"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">Data kategori belum tersedia.</td>
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