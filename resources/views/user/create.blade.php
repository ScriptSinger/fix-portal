<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Registration Page (v2)</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/adminlte.css') }}">
</head>

<body class="register-page" style="min-height: 542px;">
    <div class="register-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1>Регистрация</h1>
            </div>
            <div class="card-body">

                <form action="{{ route('register.store') }}" method="post">
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
                            class="@error('password') is-invalid @enderror form-control" placeholder="Retype password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="agreeTerms" name="terms" value="agree">
                                <label for="agreeTerms">
                                    I agree to the <a href="#">terms</a>
                                </label>
                            </div>
                        </div>

                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>

                    </div>
                </form>
                <div class="social-auth-links text-center">
                    <a href="#" class="btn btn-block btn-primary">
                        <i class="fab fa-facebook mr-2"></i>
                        Sign up using Facebook
                    </a>
                    <a href="#" class="btn btn-block btn-danger">
                        <i class="fab fa-google-plus mr-2"></i>
                        Sign up using Google+
                    </a>
                </div>
                <a href="#" class="text-center">I already have a membership</a>
            </div>

        </div>
    </div>
    <script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>
</body>

</html>
