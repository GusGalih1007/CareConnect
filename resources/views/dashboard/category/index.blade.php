@extends('layout.admin.admin')
@section('title', 'List Kategori')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">List Data Kategori</h4>
                    </div>
                    <div>
                        <a href="{{ route('admin.category.trash') }}" class="btn btn-info">Tong Sampah</a>
                        <a href="{{ route('admin.category.create') }}" class="btn btn-primary">Tambah</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="custom-datatable-entries">
                        <table id="datatable" class="table-bordered table-hover table" data-toggle="data-table">
                            <thead>
                                <tr>
                                    <td>No.</td>
                                    <td>Nama Kategory</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->category_name }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.category.edit', $item->category_id) }}" class="btn btn-warning btn-sm">Edit</a>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                data-bs-target="#deleteData{{ $item->category_id }}"
                                                data-bs-toggle="modal">Hapus</button>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="deleteData{{ $item->category_id }}" tabindex="-1"
                                        aria-labelledby="deleteDataLabel" aria-hidden="true" data-bs-backdrop="static"
                                        data-bs-keyboard="false">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.category.delete', $item->category_id) }}"
                                                    method="POST">
                                                    {{ csrf_field() }}
                                                    @method('DELETE')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteDataLabel">
                                                            Delete Confirmation</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="text-bold">
                                                            Apakah anda serius ingin menghapus {{ $item->category_name }}?
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batalkan</button>
                                                        <button type="submit" class="btn btn-danger">Iya</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
