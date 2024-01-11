@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Редактировать</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Пользователи</a></li>
                            <li class="breadcrumb-item active">Редактировать</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <form method="POST" action="{{ route('users.update', ['user' => $user->id]) }}" id="quickForm"
                novalidate="novalidate" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">

                                <div class="card-tools">
                                    <!-- This will cause the card to maximize when clicked -->
                                    <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                            class="fas fa-expand"></i></button>
                                </div>
                            </div>

                            <div class="card-body">

                                <input type="hidden" name="id" value="{{ $user->id }}">

                                <div class="form-group">
                                    <label for="InputName">Имя</label>
                                    <input type="text" name="name" value="{{ $user->name }}"
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
                                    <input disabled type="text" value="{{ $user->email }}"
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
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Обновить</button>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">

                                <div class="card-tools">
                                    <!-- This will cause the card to maximize when clicked -->
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
                                        placeholder="location" aria-describedby="InputLocation-error" aria-invalid="true">
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



                                <div><img class="img-thumbnail"
                                        src="{{ $user->avatar !== null ? asset('storage/' . $user->avatar) : asset('assets/front/images/avatar.png') }}"
                                        alt="">
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
            </form>
        </section>
    </div>
@endsection
