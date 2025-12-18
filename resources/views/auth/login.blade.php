@extends('layout.auth.temp1')
@section('title', 'Login')
@section('content')
    <div class="row m-0 align-items-center bg-white vh-100">
        <div class="col-md-6">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
                        <div class="card-body z-3 px-md-0 px-lg-4">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <span class="text-success">{{ session('success') }}</span>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <span class="text-danger">{{ session('error') }}</span>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            <h2 class="mb-2 text-center">Log in</h2>
                            <p class="text-center">Login untuk memulai donasi.</p>
                            <form action="{{ route('login.post') }}" method="POST" class="needs-validation" novalidate>
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" id="email"
                                                aria-describedby="email" placeholder="Masukan Email" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Alamat email perlu diisi!
                                        </div>
                                        @if ($errors->has('email'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('email') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" id="password"
                                                aria-describedby="password" placeholder="Password" required>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">
                                        Password perlu diisi
                                    </div>
                                    @if ($errors->has('password'))
                                        <div class="alert alert-danger">
                                            {{ $errors->first('password') }}
                                        </div>
                                    @endif
                                    <div class="col-lg-12 d-flex justify-content-between">
                                        <div class="form-check mb-3">
                                        <input type="checkbox" name="remember_me" value="true" class="form-check-input" id="customCheck1">
                                        <label class="form-check-label" for="customCheck1">Remember Me</label>
                                    </div>
                                        <a href="{{ route('forgot-password.form') }}">Lupa password?</a>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-primary">Log in</button>
                                </div>
                                <p class="mt-3 text-center">
                                    Tidak punya akun? <a href="{{ route('register.form') }}" class="text-underline">Silahkan
                                        registrasi</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sign-bg">
                <svg width="280" height="230" viewBox="0 0 431 398" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.05">
                        <rect x="-157.085" y="193.773" width="543" height="77.5714" rx="38.7857"
                            transform="rotate(-45 -157.085 193.773)" fill="#3B8AFF" />
                        <rect x="7.46875" y="358.327" width="543" height="77.5714" rx="38.7857"
                            transform="rotate(-45 7.46875 358.327)" fill="#3B8AFF" />
                        <rect x="61.9355" y="138.545" width="310.286" height="77.5714" rx="38.7857"
                            transform="rotate(45 61.9355 138.545)" fill="#3B8AFF" />
                        <rect x="62.3154" y="-190.173" width="543" height="77.5714" rx="38.7857"
                            transform="rotate(45 62.3154 -190.173)" fill="#3B8AFF" />
                    </g>
                </svg>
            </div>
        </div>
        <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
            <img src="{{ asset('hope-ui/html/assets/images/auth/01.png') }}" class="img-fluid gradient-main animated-scaleX"
                alt="images">
        </div>
    </div>
@endsection
