@extends('public.auth.layouts.app')
@section('title', 'Регистрация | ' . config('app.name', 'Ufamasters'))
@section('body-class', 'register-page')
@section('body-style', 'min-height: 542px;') <!-- Устанавливаем атрибут style для body -->
@section('content')
    <div class="register-box">
        <div class="login-logo">
            <a href="{{ route('articles.index') }}"><b>Ufa</b>masters</a>
        </div>
        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Зарегистрировать новый аккаунт</p>
                <form action="{{ route('register') }}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input name="name" type="text" class="@error('name') is-invalid @enderror form-control"
                            placeholder="Имя" value="{{ old('name') }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        <span id="InputText-error" class="error invalid-feedback">
                            @if ($errors->any())
                                {{ $errors->first('name') }}
                            @endif
                        </span>
                    </div>
                    <div class="input-group mb-3">
                        <input name="email" type="email" class="@error('email') is-invalid @enderror form-control"
                            placeholder="Email" value="{{ old('email') }}">
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
                            placeholder="Password" autocomplete="new-password">
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
                            class="@error('password') is-invalid @enderror form-control" placeholder="Retype password">
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

                    <div class="icheck-primary">
                        <input type="checkbox" id="agreeTerms" name="terms" value="agree">
                        <label for="agreeTerms">
                            Я согласен с <a data-toggle="modal" data-target="#termsModal" href="#">условиями</a>
                        </label>
                    </div>
                    <button id="registerButton" type="submit"
                        class="btn btn-block btn-dark btn-flat">Зарегистрироваться</button>
                </form>
                <div class="social-auth-links text-center">
                    <a href="#" class="btn btn-block btn-primary btn-flat">
                        <i class="fab fa-facebook mr-2"></i>
                        Sign up using Facebook
                    </a>
                    <a href="#" class="btn btn-block btn-danger btn-flat">
                        <i class="fab fa-google-plus mr-2"></i>
                        Sign up using Google+
                    </a>
                </div>
                <a href="{{ route('login') }}" class="text-center">У меня уже есть аккаунт</a>
            </div>

        </div>
    </div>

    @include('public.auth.personal-policy')
@endsection

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/agree.js') }}"></script>
@endpush
