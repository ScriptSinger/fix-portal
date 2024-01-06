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
                    <p>
                        {{ session('status') }}
                    </p>
                @else
                    <p class="login-box-msg">Забыли пароль? Здесь вы можете легко восстановить его.</p>
                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" name="email"
                                class="@error('email') is-invalid @enderror form-control form-control" placeholder="Email">
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
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-block btn-dark btn-flat">Запросить новый
                                    пароль</button>
                            </div>

                        </div>
                    </form>
                    <p class="mt-3 mb-1">
                        <a href="{{ route('login') }}">Вход</a>
                    </p>
                    <p class="mb-0">
                        <a href="{{ route('register') }}" class="text-center">Зарегистрировать новый аккаунт</a>
                    </p>
                @endif
            </div>

        </div>

    </div>

@endsection
