@extends('layout.admin.admin')
@section('title', 'Persutujuan Donasi')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">List Data Persutujuan Donasi</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="custom-datatable-entries">
                        <table id="datatable" class="table table-bordered table-hover" data-toggle="data-table">
                            <thead>
                                <tr>
                                    <td>No.</td>
                                    <td>Nama Permintaan</td>
                                    <td>Permintaan Oleh</td>
                                    <td>Tipe Donasi</td>
                                    <td>Prioritas</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ $item->user->username }}</td>
                                        <td>
                                            {{ $item->donation_type->value == 'multiple_items' ? 'Multiple Items' : 'Single Item' }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $item->priority->value == 'urgent' ? 'danger' : ($item->priority->value == 'normal' ? 'primary' : 'secondary') }}">
                                                {{ ucfirst($item->priority->value) }}
                                            </span>
                                        </td>
                                        <td><span
                                                class="badge bg-{{ $item->status->value == 'active' ? 'success' : ($item->status->value == 'need_revision' ? 'info' : ($item->status->value == 'rejected' ? 'danger' : 'warning')) }}">
                                                {{ ucfirst($item->status->value) }}
                                            </span></td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.donation-validation.show', $item->donation_request_id) }}"
                                                class="btn btn-info btn-sm">
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
