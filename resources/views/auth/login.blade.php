@extends('layouts.app')

@section('body-class', 'login-page')
@section('body-style', 'min-height: 466px;')

@section('content')


    <div class="login-box">
        <div class="card card-warning">
            <div class="card-header text-center">
                <h1>Вход</h1>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Войдите, чтобы начать сеанс</p>
                <form action="{{ route('login') }}" method="post">
                    @csrf

                    <div class="input-group mb-3">
                        <input name="email" type="email" class="@error('email') is-invalid @enderror form-control"
                            placeholder="Email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        <span id="InputText-error" class="error invalid-feedback">
                            @if ($errors->any())
                                {{ $errors->first('email') }}
                            @endif
                        </span>
                    </div>
                    <div class="input-group mb-3">
                        <input name="password" type="password" class="@error('password') is-invalid @enderror form-control"
                            placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <span id="InputText-error" class="error invalid-feedback">
                            @if ($errors->any())
                                {{ $errors->first('password') }}
                            @endif
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-4">
                            <button type="submit" class="btn btn-warning btn-block ">Войти</button>
                        </div>

                    </div>
                </form>
                <div class="social-auth-links text-center mt-2 mb-3">
                    <a href="#" class="btn btn-block btn-primary">
                        <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
                    </a>
                    <a href="#" class="btn btn-block btn-danger">
                        <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
                    </a>
                </div>

                <p class="mb-1">
                    <a href="forgot-password.html">I forgot my password</a>
                </p>
                <p class="mb-0">
                    <a href="{{ route('register') }}" class="text-center">Register a new membership</a>
                </p>
            </div>
        </div>
    </div>

@endsection
