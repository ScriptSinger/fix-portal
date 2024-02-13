@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>
                            Редактировать #{{ $reply->id }}
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.replies.index') }}">Ответы</a></li>
                            <li class="breadcrumb-item active">Редактировать #{{ $reply->id }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="user-block">
                            <img class="img-circle"
                                src="{{ $reply->user->avatar !== null ? asset('storage/' . $reply->user->avatar) : asset('assets/front/images/avatar.png') }}"
                                alt="User Image">
                            <span class="username"><a
                                    href="{{ route('admin.users.edit', ['user' => $reply->user->id]) }}">{{ $reply->user->name }}
                                    #
                                    {{ $reply->user->id }}</a></span>
                            <span class="description">Shared publicly - 7:30 PM Today</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.replies.update', ['reply' => $reply->id]) }}"
                        id="quickForm" novalidate="novalidate">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label>Содержание</label>
                                <textarea name="text" class="form-control basic" data-upload-url="{{ route('api.summernote.upload') }}">{{ $reply->text }}</textarea>

                            </div>
                            <div class="form-group">
                                <label>Created At</label>
                                <input type="text" class="form-control" disabled value="{{ $reply->created_at }}">
                            </div>
                            <div class="form-group">
                                <label>Updated At</label>
                                <input type="text" class="form-control" disabled value="{{ $reply->updated_at }}">
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-danger"
                                        onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
                                        Удалить
                                    </a>
                                    <button type="submit" class="btn btn-primary float-right">Обновить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form id="delete-form" class="d-none"
                        action="{{ route('admin.categories.destroy', ['category' => $reply->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit" onclick="return confirm('Подтвердите удаление')">
                            Удалить
                        </button>
                    </form>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/summernote/basic.js') }}"></script>
@endpush
