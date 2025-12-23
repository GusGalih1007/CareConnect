@extends('layout.admin.admin')
@section('title', Auth::user()->username . ' Profile')
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="profile-img position-relative mb-lg-0 profile-logo profile-logo1 mb-3 me-3">
                                @if (Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="User-Profile"
                                        class="img-fluid rounded-pill avatar-100">
                                @else
                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/01.png') }}" alt="User-Profile"
                                        class="theme-color-default-img img-fluid rounded-pill avatar-100">
                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/avtar_1.png') }}"
                                        alt="User-Profile" class="theme-color-purple-img img-fluid rounded-pill avatar-100">
                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/avtar_2.png') }}"
                                        alt="User-Profile" class="theme-color-blue-img img-fluid rounded-pill avatar-100">
                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/avtar_4.png') }}"
                                        alt="User-Profile" class="theme-color-green-img img-fluid rounded-pill avatar-100">
                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/avtar_5.png') }}"
                                        alt="User-Profile" class="theme-color-yellow-img img-fluid rounded-pill avatar-100">
                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/avtar_3.png') }}"
                                        alt="User-Profile" class="theme-color-pink-img img-fluid rounded-pill avatar-100">
                                @endif
                            </div>
                            <div class="d-flex align-items-center mb-sm-0 mb-3 flex-wrap">
                                <h4 class="h4 me-2">{{ Auth::user()->username }}</h4>
                                <span> - {{ Auth::user()->role->role_name }}</span>
                            </div>
                        </div>
                        <ul class="d-flex nav nav-pills profile-tab mb-0 text-center" data-toggle="slider-tab"
                            id="profile-pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active show" data-bs-toggle="tab" href="#profile-profile" role="tab"
                                    aria-selected="false">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#profile-change-password" role="tab"
                                    aria-selected="false">Ganti Password</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="profile-content tab-content">
                <div id="profile-change-password" class="tab-pane fade">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Ganti Password</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.profile.changePassword') }}" method="post"
                                class="needs-validation" novalidate>
                                {{ csrf_field() }}
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Password Saat Ini</label>
                                            <input type="password" class="form-control" id="current_password"
                                                name="current_password" required>
                                            <div class="invalid-feedback">Kamu perlu memasukan password lama</div>
                                            @error('current_password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password Baru</label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror" id="password"
                                                name="password" required>
                                            <div class="invalid-feedback">Password baru wajib diisi</div>
                                            @error('password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                            <input type="password" class="form-control" id="password_confirmation"
                                                name="password_confirmation" required>
                                            <div class="invalid-feedback">Password baru harus dikonfirmasi</div>
                                            @error('password_confirmation')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <small>
                                        Harap pastikan agar password baru anda tidak sama dengan password lama anda.
                                         Anda juga akan logout dan diharapkan login kembali dengan password baru
                                    </small>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-warning">
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="profile-profile" class="tab-pane fade active show">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div></div>
                                <ul class="d-flex nav nav-pills profile-tab mb-0 text-center" data-toggle="slider-tab"
                                    id="profile-pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active show" data-bs-toggle="tab" href="#view"
                                            role="tab" aria-selected="false">View</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#edit" role="tab"
                                            aria-selected="false">Edit</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div id="view" class="tab-pane fade active show">
                            <div class="card">
                                <div class="card-header">
                                    <div class="header-title">
                                        <h4 class="card-title">Profile</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="user-profile">
                                            @if (Auth::user()->avatar)
                                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                                    alt="profile-img" class="rounded-pill avatar-130 img-fluid">
                                            @else
                                                <img src="{{ asset('hope-ui/html/assets/images/avatars/01.png') }}"
                                                    alt="profile-img" class="rounded-pill avatar-130 img-fluid">
                                            @endif
                                        </div>
                                        <div class="mt-3">
                                            <h3 class="d-inline-block">{{ Auth::user()->username }}</h3>
                                            <p class="d-inline-block pl-3"> - {{ Auth::user()->role->role_name }}</p>
                                            <div class="user-bio">
                                                <p>{{ Auth::user()->bio ?? 'No Biodata' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <div class="header-title">
                                        <h4 class="card-title">Tentang Pengguna</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mt-2">
                                        <h6 class="mb-1">Bergabung:</h6>
                                        <p>{{ Auth::user()->created_at->format('Y/m/d') }}</p>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="mb-1">Alamat:</h6>
                                        <ul>
                                            @forelse (Auth::user()->location as $location)
                                                <li>{{ $location->address }}</li>
                                            @empty
                                                <p>Kosong</p>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="mb-1">Email:</h6>
                                        <p><a href="#" class="text-body"> {{ Auth::user()->email }}</a></p>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="mb-1">Tipe Pengguna:</h6>
                                        <p>{{ Auth::user()->user_type ?? 'Administrator' }}</p>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="mb-1">No. Telp:</h6>
                                        <p><a href="#" class="text-body">{{ Auth::user()->phone }}</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="edit" class="tab-pane fade">
                            <form action="{{ route('admin.profile.update') }}" method="post"
                                enctype="multipart/form-data" class="needs-validation" novalidate>
                                {{ csrf_field() }}
                                @method('PUT')
                                <div class="card">
                                    <div class="card-header">
                                        <div class="header-title">
                                            <h4 class="card-title">Edit Profile</h4>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="align-items-center text-center">
                                            <div class="user-profile">
                                                @if (Auth::user()->avatar)
                                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                                        alt="profile-img" class="rounded-pill avatar-130 img-fluid">
                                                @else
                                                    <img src="{{ asset('hope-ui/html/assets/images/avatars/01.png') }}"
                                                        alt="profile-img" class="rounded-pill avatar-130 img-fluid">
                                                @endif
                                            </div>
                                            <center>
                                                <div class="mt-3">
                                                    <div class="form-group col-lg-3 col-md-6 col-sm-12">
                                                        <input type="file" name="avatar" class="form-control" />
                                                    </div>
                                                    </divc>
                                                    <div class="mt-3">
                                                        <div class="form-group col-lg-3 col-md-6 col-sm-12">
                                                            <input class="form-control text-center" name="username"
                                                                value="{{ Auth::user()->username }}">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <textarea class="form-control text-center" name="bio" id="description" rows="5" cols="5"
                                                                placeholder="Biodata">{{ Auth::user()->bio }}</textarea>
                                                        </div>
                                                    </div>
                                            </center>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <div class="header-title">
                                            <h4 class="card-title">Tentang Pengguna</h4>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mt-2">
                                            <h6 class="mb-1">Bergabung:</h6>
                                            <p>{{ Auth::user()->created_at->format('Y/m/d') }}</p>
                                        </div>
                                        <div class="mt-2">
                                            <h6 class="mb-1">Alamat:</h6>
                                            <div class="row">
                                                @forelse (Auth::user()->location as $index => $location)
                                                    <div class="form-group col-md-6 col-sm-12">
                                                        <input type="hidden" name="location[{{ $index }}][id]"
                                                            value="{{ $location->location_id }}">
                                                        <textarea class="form-control" name="location[{{ $index }}][address]">{{ $location->address }}</textarea>
                                                    </div>
                                                    <div class="form-group col-md-6 col-sm-12">
                                                        <a href="#deleteAddress{{ $location->location_id }}"
                                                            class="btn btn-danger btn-sm" data-bs-toggle="modal">Hapus Alamat</a>
                                                    </div>
                                                    <div class="modal fade"
                                                        id="deleteAddress{{ $location->location_id }}" tabindex="-1"
                                                        aria-labelledby="deleteAddressLabel" aria-hidden="true"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="{{ route('admin.location.delete', $location->location_id) }}"
                                                                    method="POST">
                                                                    {{ csrf_field() }}
                                                                    @method('DELETE')
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteAddressLabel">
                                                                            Delete Confirmation</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="text-bold">
                                                                            Apakah anda serius ingin menghapus alamat ini?
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
                                                @empty
                                                    <p>Empty</p>
                                                @endforelse
                                            </div>
                                            <a href="#addAddress" class="btn btn-primary btn-sm"
                                                data-bs-toggle="modal">Tambah Alamat</a>
                                        </div>
                                        <div class="mt-2">
                                            <h6 class="mb-1">Email:</h6>
                                            <div class="form-group col-md-6 col-sm-12">
                                                <input type="email" name="email" class="form-control"
                                                    value="{{ Auth::user()->email }}" />
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <h6 class="mb-1">Tipe Pengguna:</h6>
                                            <p>{{ Auth::user()->user_type ?? 'Administrator' }}</p>
                                        </div>
                                        <div class="mt-2">
                                            <h6 class="mb-1">No. Telp:</h6>
                                            <div class="form-group col-md-6 col-sm-12">
                                                <input type="tel" name="phone" class="form-control"
                                                    value="{{ Auth::user()->phone }}" />
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                Simpan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal fade" id="addAddress" tabindex="-1" aria-labelledby="addAddressLabel"
                            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="{{ route('admin.location.store') }}" class="needs-validation" novalidate
                                        method="POST">
                                        {{ csrf_field() }}
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addAddressLabel">Tambah Alamat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="address" class="form-label">Alamat Baru</label>
                                                            <textarea name="address" class="form-control" id="address" cols="10" rows="10"
                                                                placeholder="Alamat baru" required></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batalkan</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
