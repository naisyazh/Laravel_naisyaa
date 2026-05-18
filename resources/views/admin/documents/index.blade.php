@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Manajemen Sertifikat & Undangan User</h4>
                <p class="card-description">Kelola dokumen eksklusif untuk tiap user</p>
                
                <a href="{{ route('documents.create') }}" class="btn btn-gradient-primary btn-fw mb-3">
                    <i class="mdi mdi-upload"></i> Unggah Dokumen Baru
                </a>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Jenis</th>
                                <th>File</th>
                                <th>Tanggal Unggah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $doc)
                            <tr>
                                <td>{{ $doc->user_name }}</td>
                                <td>
                                    <label class="badge {{ $doc->type == 'sertifikat' ? 'badge-info' : 'badge-danger' }}">
                                        {{ ucfirst($doc->type) }}
                                    </label>
                                </td>
                                <td>
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-light">
                                        <i class="mdi mdi-eye"></i> Lihat
                                    </a>
                                </td>
                                <td>{{ date('d M Y', strtotime($doc->created_at)) }}</td>
                                <td>
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-inverse-danger" onclick="return confirm('Hapus dokumen ini?')">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada dokumen yang diunggah.</td>
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