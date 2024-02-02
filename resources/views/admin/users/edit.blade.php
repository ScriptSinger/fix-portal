@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">

                    <div class="col-sm-6">
                        <div class="user-block">
                            <img class="img-circle"
                                src="{{ optional($user)->avatar ? asset('storage/' . $user->avatar) : asset('assets/front/images/avatar.png') }}"
                                alt="User Image">
                            <span class="username"><a href="#">{{ $user->name }} # {{ $user->id }}</a></span>
                            <span class="description">Shared publicly - 7:30 PM Today</span>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Пользователи</a></li>
                            <li class="breadcrumb-item active">{{ $user->name }} # {{ $user->id }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <form method="POST" action="{{ route('admin.users.update', ['user' => $user->id]) }}" novalidate="novalidate"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                            class="fas fa-expand"></i></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" name="name" value="{{ $user->name }}"
                                        class="form-control @error('name') is-invalid @enderror" placeholder="Имя">
                                    <span id="InputName-error" class="error invalid-feedback">
                                        @if ($errors->any())
                                            @foreach ($errors->all() as $error)
                                                {{ $error }}
                                            @endforeach
                                        @endif
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input disabled type="text" value="{{ $user->email }}" class="form-control"
                                        placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <label>Регистрация</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="reservation"
                                            value="{{ $user->created_at }}" disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Верификация</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="reservation"
                                            value="{{ $user->email_verified_at }}" disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Редактирование</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="reservation"
                                            value="{{ $user->updated_at }}" disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Удален</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="reservation"
                                            value="{{ $user->deleted_at }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-primary   card-widget widget-user-2 shadow-sm">
                            <div class="card-header">
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                            class="fas fa-expand"></i></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="InputPhone">Телефон</label>
                                    <input type="text" name="phone" value="{{ $user->phone }}"
                                        class="form-control @error('phone') is-invalid @enderror" id="InputName"
                                        placeholder="Телефон" aria-describedby="InputPhone-error" aria-invalid="true">
                                    <span id="InputName-error" class="error invalid-feedback">
                                        @if ($errors->any())
                                            @foreach ($errors->all() as $error)
                                                {{ $error }}
                                            @endforeach
                                        @endif
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="InputLocation">Город</label>
                                    <input type="text" name="location" value="{{ $user->location }}"
                                        class="form-control @error('location') is-invalid @enderror" id="InputLocation"
                                        placeholder="location" aria-describedby="InputLocation-error"
                                        aria-invalid="true">
                                    <span id="InputLocation-error" class="error invalid-feedback">
                                        @if ($errors->any())
                                            @foreach ($errors->all() as $error)
                                                {{ $error }}
                                            @endforeach
                                        @endif
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="InputTextArea">Биография</label>
                                    <textarea id="InputTextArea" name="bio" class="form-control @error('bio') is-invalid @enderror" rows="3">{{ $user->bio }}</textarea>
                                    <span id="InputTextArea-error" class="error invalid-feedback">
                                        @error('bio')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                                <div class="custom-file w-100 mb-4">
                                    <label class="custom-file-control" for="avatar"> </label>
                                    <input name="avatar" type="file" class="w-100" id="avatar">
                                    <span class="form-control-feedback">
                                        @error('avatar')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                <a href="#" class="btn btn-danger"
                                    onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
                                    Удалить
                                </a>
                                <button type="submit" class="btn btn-primary float-right">Редактировать</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="delete-form" class="d-none" action="{{ route('admin.users.destroy', ['user' => $user->id]) }}"
                method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit" onclick="return confirm('Подтвердите удаление')">
                    Удалить
                </button>
            </form>
    </div>
@endsection
