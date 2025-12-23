@extends('layout.admin.admin')
@section('title', 'Sampah Kategori')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Kotak Sampah Kategori</h4>
                    </div>
                    <div class="">
                        <a href="{{ route('admin.category.index') }}" class="btn btn-light">Kembali</a>
                        <a href="{{ route('admin.category.restoreAll') }}" class="btn btn-success">Pulihkan semua</a>
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
                                            <button type="button" class="btn btn-success btn-sm"
                                                data-bs-target="#restoreData{{ $item->category_id }}"
                                                data-bs-toggle="modal">Pulihkan</button>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="restoreData{{ $item->category_id }}" tabindex="-1"
                                        aria-labelledby="restoreDataLabel" aria-hidden="true" data-bs-backdrop="static"
                                        data-bs-keyboard="false">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="restoreDataLabel">
                                                            Restore Confirmation</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="text-bold">
                                                            Apakah anda serius ingin Memulihkan {{ $item->category_name }}?
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batalkan</button>
                                                        <a href="{{ route('admin.category.restoreOne', $item->category_id) }}" class="btn btn-success">Iya</a>
                                                    </div>
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
