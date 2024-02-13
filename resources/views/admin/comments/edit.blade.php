@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Редактировать #{{ $comment->id }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.comments.index') }}">Комментарии</a></li>
                            <li class="breadcrumb-item active">Редактировать #{{ $comment->id }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <div class="user-block">
                                <img class="img-circle"
                                    src="{{ $comment->user->avatar !== null ? asset('storage/' . $comment->user->avatar) : asset('assets/front/images/avatar.png') }}"
                                    alt="User Image">
                                <span class="username"><a
                                        href="{{ route('admin.users.edit', ['user' => $comment->user->id]) }}">{{ $comment->user->name }}
                                        #
                                        {{ $comment->user->id }}</a></span>
                                <span class="description">Создан - {{ $comment->dateAsCarbon->diffForHumans() }}</span>
                            </div>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                        class="fas fa-expand"></i></button>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.comments.update', ['comment' => $comment->id]) }}"
                            id="quickForm" novalidate="novalidate">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Содержание</label>
                                    <textarea name="text" class="form-control basic" data-upload-url="{{ route('api.summernote.upload') }}">{{ $comment->text }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Created At</label>
                                    <input type="text" class="form-control" disabled value="{{ $comment->created_at }}">
                                </div>
                                <div class="form-group">
                                    <label>Updated At</label>
                                    <input type="text" class="form-control" disabled value="{{ $comment->updated_at }}">
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
                            action="{{ route('admin.categories.destroy', ['category' => $comment->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit" onclick="return confirm('Подтвердите удаление')">
                                Удалить
                            </button>
                        </form>

                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            Ответы
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                        class="fas fa-expand"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                                data-locale={{ asset('assets/locale/datatable/russian.json') }}
                                data-routes='{
                                        "index": "{{ route('api.replies.index', ['comment' => $comment->id]) }}",
                                        "edit": "{{ route('admin.replies.edit', ['reply' => ':id']) }}",
                                        "userEdit": "{{ route('admin.users.edit', ['user' => ':id']) }}",
                                        "destroy": "{{ route('api.replies.destroy', ['reply' => ':id']) }}",
                                        "restore": "{{ route('api.replies.restore', ['reply' => ':id']) }}"
                                    }'>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/summernote/basic.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom/datatables/commentReplies.js') }}"></script>
@endpush
