@extends('public.auth.layouts.app')
@section('title', 'Вход | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')
@section('body-class', 'login-page')
@section('body-style', 'min-height: 466px;')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ route('articles.index') }}"><b>Ufa</b>masters</a>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            @if (session('status'))
            <p role="alert" class="login-box-msg"> {{ session('status') }}</p>
            @else
            <p class="login-box-msg">Войдите, чтобы начать сеанс</p>
            @endif
            <form action="{{ route('login') }}" method="POST">
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

                <button type="submit" class="btn btn-block btn-dark btn-flat">Войти</button>

            </form>
            {{--
            <div class="social-auth-links text-center mt-2 mb-3">
                <a href="{{ route('auth.redirect', ['provider' => 'google']) }}" class="btn btn-block btn-primary btn-flat">
                    <i class="fab fa-google mr-2"></i> Continue with Google
                </a>
            </div>
            --}}

            <p class="mb-1">
                <a href="{{ route('password.request') }}">Забыли пароль?</a>
            </p>
            <p class="mb-0">
                <a href="{{ route('register') }}" class="text-center">Зарегистрировать новый аккаунт</a>
            </p>

        </div>
    </div>
</div>

@endsection
