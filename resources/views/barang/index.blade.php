@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <div>
                        <h4 class="card-title mb-1">Master Buku Toko</h4>
                        <p class="text-muted mb-0">Kelola barang, cetak label barcode, lalu uji hasilnya lewat kamera scanner.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('barang.scanner') }}" class="btn btn-gradient-info btn-fw">
                            <i class="mdi mdi-barcode"></i> Scan Barcode
                        </a>
                        <button type="button" class="btn btn-gradient-primary btn-fw"
                                data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="mdi mdi-plus"></i> Tambah Buku Toko
                        </button>
                    </div>
                </div>
                <form action="{{ route('barang.cetak') }}" method="POST">
                    @csrf

                    <div class="row mb-3 bg-light p-3 rounded">
                        <div class="col-md-3">
                            <label>Mulai Kolom Label (X)</label>
                            <input type="number" name="x" class="form-control"
                                   min="1" max="5" value="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>Mulai Baris Label (Y)</label>
                            <input type="number" name="y" class="form-control"
                                   min="1" max="8" value="1" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit"
                                    class="btn btn-gradient-danger">
                                Cetak Label Harga Buku
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped" id="barangTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Kode Buku</th>
                                    <th>Judul Buku</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangs as $b)
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                               name="selected_ids[]"
                                               value="{{ $b->id_barang }}">
                                    </td>
                                    <td>{{ $b->id_barang }}</td>
                                    <td>{{ $b->nama }}</td>
                                    <td>
                                        Rp {{ number_format($b->harga, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $b->is_active ? 'badge-gradient-success' : 'badge-gradient-secondary' }}">
                                            {{ $b->is_active ? 'Aktif Dijual' : 'Tidak Dijual' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group">
                                            <button type="button"
                                                    class="btn btn-sm btn-inverse-warning btn-edit"
                                                    data-id="{{ $b->id_barang }}">
                                                Edit
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-inverse-danger btn-delete"
                                                    data-id="{{ $b->id_barang }}">
                                                Hapus
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <form action="{{ route('barang.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Buku Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" class="form-control" required>
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_is_active" checked>
                    <label class="form-check-label" for="create_is_active">
                        Buku aktif dan bisa di-checkout user
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <form id="formEdit" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Buku Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="nama" id="edit_nama"
                       class="form-control mb-2" required>

                <input type="number" name="harga" id="edit_harga"
                       class="form-control" required>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                    <label class="form-check-label" for="edit_is_active">
                        Buku aktif dan tampil di halaman checkout user
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    $('#selectAll').click(function(){
        $('input[name="selected_ids[]"]').prop('checked', this.checked);
    });

    // Edit
    $('.btn-edit').click(function(){
        let id = $(this).data('id');

        $.get('/barang/' + id + '/edit', function(data){
            $('#edit_nama').val(data.nama);
            $('#edit_harga').val(data.harga);
            $('#edit_is_active').prop('checked', !!data.is_active);
            $('#formEdit').attr('action', '/barang/' + id);
            $('#modalEdit').modal('show');
        });
    });

    $('.btn-delete').click(function(){
        let id = $(this).data('id');

        if(confirm('Hapus buku ini dari toko?')){
            $('#deleteForm')
                .attr('action', '/barang/' + id)
                .submit();
        }
    });

});
</script>

@endsection
