@extends('layout.admin.admin')
@section('title', "Form Kategori")
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">
                            {{ $data ? 'Edit Category ' . $data->category_name : 'Tambah Category' }}
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ $data ? route('admin.category.update', $data->category_id) : route('admin.category.store') }}" method="post" class="needs-validation" novalidate>
                        {{ csrf_field() }}
                        @if ($data)
                            @method('PUT')
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nama Category*</label>
                                    <input class="form-control" type="text" name="name" id="name"
                                        placeholder="Masukan nama kategori..." value="{{ old('name', $data->category_name ?? '')  }}" required>
                                        <div class="invalid-feedback">
                                            Nama kategori harus diisi!
                                        </div>
                                        @if ($errors->has('name'))
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        @endif
                                </div>
                            </div>
                        </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('admin.category.index') }}" class="btn btn-light">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection
