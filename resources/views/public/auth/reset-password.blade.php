@extends('layouts.app')
@section('title', 'Восстановление пароля | ' . config('app.name', 'Ufamasters'))
@section('body-class', 'register-page')
@section('body-style', 'min-height: 542px;') <!-- Устанавливаем атрибут style для body -->
@section('content')

    <div class="login-box">
        <div class="login-logo">
            <a href="{{ route('articles.index') }}"><b>Ufa</b>masters</a>
        </div>

        <div class="card">
            <div class="card-body login-card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @else
                    <p class="login-box-msg">Вы всего в одном шаге от нового пароля, восстановите его прямо сейчас.</p>
                    <form action="{{ route('password.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="" name="email" value="{{ $request->input('email') }}" hidden>

                        <div class="input-group mb-3">
                            <input name="password" type="password"
                                class="@error('password') is-invalid @enderror form-control" placeholder="Password">
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

                        <div class="input-group mb-3">
                            <input name="password_confirmation" type="password"
                                class="@error('password_confirmation') is-invalid @enderror form-control"
                                placeholder="Confirm Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            <span id="InputText-error" class="error invalid-feedback">
                                @if ($errors->any())
                                    {{ $errors->first('password_confirmation') }}
                                @endif
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-dark btn-flat btn-block">Изменить пароль</button>
                            </div>
                        </div>
                    </form>
                    <p class="mt-3 mb-1">
                        <a href="{{ route('login') }}">Войти</a>
                    </p>
                @endif
            </div>
        </div>
    </div>

@endsection
