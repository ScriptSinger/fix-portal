@extends('public.layouts.bar')
@section('title', 'Личный кабинет | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Добро пожаловать, {{ $user->name }}<small class="hidden-xs-down hidden-sm-down">Ваш последний
                            визит:
                            12.11.2023 в 19:51
                        </small></h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('profile') }}
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.advertising')
        @include('public.layouts.widgets.sidebar.prime_categories')
    </div>
@endsection
@section('content')
    <div class="page-wrapper">
        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="data-tab" data-toggle="tab" href="#data" role="tab"
                    aria-controls="data" aria-selected="true">Личные данные</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="image-tab" data-toggle="tab" href="#image" role="tab" aria-controls="image"
                    aria-selected="false">Изображения</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="data" role="tabpanel" aria-labelledby="data-tab">
                <div class="row">
                    <div class="col-lg-6">
                        <h4>Мастер</h4>
                        <p>Если вы являетесь специалистом по ремонту бытовой техники, установите статус "мастера", чтобы
                            ваши
                            данные были доступны в разделе мастеров.</p>
                    </div>
                    <div class="col-lg-6">
                        <h4>Участник</h4>
                        <p>Для ограничения доступа к вашим данным, выберите статус "участника", который не позволит
                            публиковать вашу
                            информацию.</p>
                    </div>
                </div>
                <form method="POST" enctype="multipart/form-data" action="{{ route('profile.update') }}"
                    class="form-wrapper">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Имя</label>
                        <input name="name" class="p-1 px-2 w-100  @error('name') is-invalid  @enderror"
                            value="{{ $user->name }}" type="text" id="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Статус</label>
                        <select class="custom-select w-100" name="role_id">
                            @foreach ($roles as $key => $value)
                                <option value="{{ $key }}" @if ($key == $user->role_id) selected @endif>
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback">
                            @error('role_id')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input disabled type="text" class="p-1 px-2 w-100" placeholder="{{ $user->email }}"
                            id="email">
                    </div>
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input name="phone" type="text" class="p-1 px-2 w-100  @error('phone') is-invalid @enderror"
                            value="{{ $user->phone }}" id="phone">
                        <span class="invalid-feedback">
                            @error('phone')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="form-group">
                        <label>Город</label>
                        <input name="location" type="text"
                            class="p-1 px-2 w-100  @error('location') is-invalid @enderror" placeholder="Местоположение"
                            value="{{ $user->location }}" id="location">
                        <span class="invalid-feedback">
                            @error('location')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="bio">О себе</label>
                        <textarea name="bio" class="p-1 px-2 w-100" id="bio" placeholder="не болеее 512 символов">{{ $user->bio }}</textarea>
                        <span class="invalid-feedback">
                            @error('bio')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <button role="button" type="submit" class="btn">Обновить</button>
                </form>
            </div>

            <div class="tab-pane fade" id="image" role="tabpanel" aria-labelledby="image-tab">
                <form method="POST" enctype="multipart/form-data" class="dropzone mb-3" id="upload-form"
                    data-routes='{
                        "upload": "{{ route('api.avatars.upload') }}",
                        "show": "{{ route('api.avatars.show') }}",
                        "destroy": "{{ route('api.avatars.destroy') }}"
                        }'>

                    @csrf
                </form>
                <button id="submit" class="btn btn-dark">Отправить</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/dropzone/avatar.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#phone").inputmask({
                "mask": "+7 (999) 999-99-99"
            });
        });
    </script>
@endpush
