@extends('layouts.app')

@section('title', 'Koleksi Buku')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Koleksi Buku</h4>
                <p class="card-description"> Daftar seluruh buku di perpustakaan </p>
                
                {{-- Tombol Tambah (Hanya Admin) --}}
                @if(Auth::user()->role == 'admin')
                <a href="{{ route('buku.create') }}" class="btn btn-gradient-primary btn-fw mb-3">
                    <i class="mdi mdi-plus"></i> Tambah Buku
                </a>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> Kode </th>
                                <th> Judul Buku </th>
                                <th> Pengarang </th>
                                <th> Kategori </th>
                                {{-- Kolom Aksi (Hanya Admin) --}}
                                @if(Auth::user()->role == 'admin')
                                <th> Aksi </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buku as $b)
                            <tr>
                                <td> 
                                    <label class="badge badge-gradient-info">{{ $b->kode }}</label>
                                </td>
                                <td> {{ $b->judul }} </td>
                                <td> {{ $b->pengarang }} </td>
                                <td> {{ $b->kategori->nama_kategori ?? '-' }} </td>
                                
                                {{-- Tombol Aksi (Hanya Admin) --}}
                                @if(Auth::user()->role == 'admin')
                                <td>
                                    <a href="{{ route('buku.edit', $b->idbuku) }}" class="btn btn-inverse-info btn-sm btn-icon-text">
                                        <i class="mdi mdi-pencil btn-icon-prepend"></i> Edit
                                    </a>

                                    <form action="{{ route('buku.destroy', $b->idbuku) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus buku ini?')">
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
                                <td colspan="5" class="text-center">Belum ada koleksi buku.</td>
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