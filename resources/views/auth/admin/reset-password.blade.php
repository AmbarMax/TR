@extends('layouts.app-admin-auth')
@section('title', 'Admin Reset Password')

@section('content')
    <div class="auth-wrapper auth-v2">
        <div class="auth-inner row m-0">
            <!-- Left Text-->
            <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
                    <img class="img-fluid" src="{{Vite::adminImage('pages/reset-password-v2.svg')}}" alt="Forgot password V2" />
                </div>
            </div>
            <!-- /Left Text-->
            <!-- Reset password-->
            <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                    <h2 class="card-title font-weight-bold mb-1">Reset Password 🔒</h2>
                    <p class="card-text mb-2">Your new password must be different from previously used passwords</p>
                    <form class="auth-reset-password-form mt-2" action="{{route('admin.password.update')}}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ request()->query('token') }}">

                        <div class="form-group">

                            <input class="form-control" id="forgot-password-email" type="text" name="email" value="{{request()->query('email')}}" hidden />

                            <div class="d-flex justify-content-between">
                                <label for="reset-password-new">New Password</label>
                            </div>
                            <div class="input-group input-group-merge form-password-toggle">
                                <input class="form-control form-control-merge" id="reset-password-new" type="password" name="password" placeholder="············" aria-describedby="reset-password-new" autofocus="" tabindex="1" />
                                <div class="input-group-append">
                                      <span class="input-group-text cursor-pointer">
                                        <i data-feather="eye"></i>
                                      </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="d-flex justify-content-between">
                                <label for="reset-password-confirm">Confirm Password</label>
                            </div>
                            <div class="input-group input-group-merge form-password-toggle">
                                <input class="form-control form-control-merge" id="reset-password-confirm" type="password" name="password_confirmation" placeholder="············" aria-describedby="reset-password-confirm" tabindex="2" />
                                <div class="input-group-append">
                                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                </div>
                            </div>
                        </div>


                        <button class="btn btn-primary btn-block" tabindex="3">Set New Password</button>
                    </form>
                    <p class="text-center mt-2">
                        <a href="{{route('admin.login.index')}}">
                            <i data-feather="chevron-left"></i> Back to login
                        </a>
                    </p>
                </div>
            </div>
            <!-- /Reset password-->
        </div>
    </div>

@endsection
