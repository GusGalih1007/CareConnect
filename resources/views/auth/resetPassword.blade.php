@extends('layout.auth.temp1')
@section('title', 'Reset Password')
@section('content')
    <div class="row m-0 align-items-center bg-white vh-100">
        <div class="col-md-6">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
                        <div class="card-body z-3 px-md-0 px-lg-4">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    <span class="text-danger">{{ session('error') }}</span>
                                </div>
                            @endif
                            <h2 class="mb-2">Reset Password</h2>
                            <p>Masukan password anda yang baru serta konfirmasi password tersebut.</p>
                            <form action="{{ route('reset-password.post') }}" method="POST" class="needs-validation" novalidate>
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-lg-12">
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
                                    <div class="col-lg-12">
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
                                </div>
                                <button type="submit" class="btn btn-primary">Reset</button>
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
