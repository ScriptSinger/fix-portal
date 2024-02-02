@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Добавить</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Пользователи</a></li>
                            <li class="breadcrumb-item active">Добавить</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.store') }}" id="quickForm" novalidate="novalidate">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="InputName">Имя</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" id="InputName"
                                    placeholder="Имя" aria-describedby="InputName-error" aria-invalid="true">
                                <span id="InputName-error" class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="InputEmail">Email</label>
                                <input type="text" name="email"
                                    class="form-control @error('email') is-invalid @enderror" id="InputEmail"
                                    placeholder="Email" aria-describedby="InputEmail-error" aria-invalid="true">
                                <span id="InputEmail-error" class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="InputText">Пароль</label>
                                <input type="text" name="password"
                                    class="form-control @error('password') is-invalid @enderror" id="InputTextTask"
                                    placeholder="Пароль" aria-describedby="InputText-error" aria-invalid="true">
                                <span id="InputText-error" class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
