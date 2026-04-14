@extends('layouts.app-admin-auth')
@section('title', 'Admin Login')

@section('content')
    <div class="auth-wrapper auth-v2">
        <div class="auth-inner row m-0">
            <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
                    <img class="img-fluid" src="{{Vite::adminImage('pages/login-v2.svg')}}" alt="Login V2">
                </div>
            </div>
            <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                    <h2 class="card-title font-weight-bold mb-1">Greetings in {{ config('app.name', 'Ambar') }}! 👋</h2>
                    <p class="card-text mb-2">Log in to your account and start the adventure</p>
                    <form class="auth-login-form mt-2" action="{{ route('admin.auth.login') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="login-email" class="form-label">Email</label>
                            <input type="text" class="form-control @error('email') is-invalid @enderror"
                                   id="login-email" name="email" placeholder="example@example.com"
                                   value="{{ old('email') }}"
                                   aria-describedby="login-email" tabindex="1" autofocus/>
                            <span class="invalid-feedback" role="alert"><strong>@error('email'){{ $message }}@enderror</strong></span>
                        </div>
                        <div class="form-group">
                            <div class="d-flex justify-content-between">
                                <label for="login-password">Password</label>
                                <a href="{{route('admin.password.request')}}">
                                    <small>Forgot Password?</small>
                                </a>
                            </div>

                            <div class="input-group input-group-merge form-password-toggle">

                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror form-control-merge"
                                       id="login-password"
                                       name="password" tabindex="2" autocomplete="password"
                                       placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                       aria-describedby="login-password"/>
                                <span class="invalid-feedback"
                                      role="alert"><strong>@error('password'){{ $message }}@enderror</strong></span>
{{--                                <div class="input-group-append" style="position: absolute; right: -1px; top: 0; height: 100%;">--}}
{{--                                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>--}}
{{--                                </div>--}}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="remember" id="remember-me"
                                       tabindex="3" @checked(old('remember'))>
                                <label class="custom-control-label" for="remember-me">Remember me</label>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block" tabindex="4">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
