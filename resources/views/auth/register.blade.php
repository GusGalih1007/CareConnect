@extends('layout.auth.temp2')
@section('title', 'Register')
@section('content')
    <div class="row m-0 align-items-center bg-white h-100">
        <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
            <img src="{{ asset('hope-ui/html/assets/images/auth/05.png') }}" class="img-fluid gradient-main animated-scaleX"
                alt="images">
        </div>
        <div class="col-md-6">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card card-transparent auth-card shadow-none d-flex justify-content-center mb-0">
                        <div class="card-body">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    <span class="text-danger">{{ session('error') }}</span>
                                </div>
                            @endif
                            <h2 class="mb-2 text-center">Register</h2>
                            <p class="text-center">Silahkan lengkapi data diri anda untuk mendaftar.</p>
                            <form action="{{ route('register.post') }}" method="POST" class="needs-validation" novalidate>
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username" id="username"
                                                placeholder="Masukan username" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Username perlu diisi
                                        </div>
                                        @if ($errors->has('username'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('username') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" id="email"
                                                placeholder="Masukan Email" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Email perlu diisi
                                        </div>
                                        @if ($errors->has('email'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('email') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">No. Telepon</label>
                                            <input type="text" class="form-control" name="phone" id="phone"
                                                placeholder="No. Telepon" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            No. telepon perlu diisi
                                        </div>
                                        @if ($errors->has('phone'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('phone') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" name="password" id="password"
                                                placeholder="Masukan password" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Password perlu diisi
                                        </div>
                                        @if ($errors->has('password'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('password') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="password_confirmation" class="form-label">Konfirmasi
                                                Password</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                id="password_confirmation" placeholder="Konfirmasi password" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Password perlu dikonfirmasi
                                        </div>
                                        @if ($errors->has('password_confirmation'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('password_confirmation') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-12 d-flex justify-content-center">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="aggree_terms" class="form-check-input" id="aggreeTerms">
                                            <label class="form-check-label" for="aggreeTerms">I agree with the terms of
                                                use</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-primary">Daftar</button>
                                </div>
                                <p class="mt-3 text-center">
                                    Sudah punya akun? <a href="{{ route('login.form') }}" class="text-underline">Log in</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sign-bg sign-bg-right">
                <svg width="280" height="230" viewBox="0 0 421 359" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.05">
                        <rect x="-15.0845" y="154.773" width="543" height="77.5714" rx="38.7857"
                            transform="rotate(-45 -15.0845 154.773)" fill="#3A57E8" />
                        <rect x="149.47" y="319.328" width="543" height="77.5714" rx="38.7857"
                            transform="rotate(-45 149.47 319.328)" fill="#3A57E8" />
                        <rect x="203.936" y="99.543" width="310.286" height="77.5714" rx="38.7857"
                            transform="rotate(45 203.936 99.543)" fill="#3A57E8" />
                        <rect x="204.316" y="-229.172" width="543" height="77.5714" rx="38.7857"
                            transform="rotate(45 204.316 -229.172)" fill="#3A57E8" />
                    </g>
                </svg>
            </div>
        </div>
    </div>
@endsection
