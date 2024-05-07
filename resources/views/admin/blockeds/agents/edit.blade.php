@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Редактировать</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">User-agents</a></li>
                            <li class="breadcrumb-item active">Редактировать</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <span># {{ $blocked->id }}</span>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.agents.update', ['agent' => $blocked->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label>User-agent</label>
                                <input type="text" name="user_agent"
                                    class="form-control @error('user_agent') is-invalid @enderror"
                                    value="{{ $blocked->user_agent }}">
                                <span class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>

                            <div class="form-group">
                                <label>Создан</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservation"
                                        value="{{ $blocked->created_at }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Изменен</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservation"
                                        value="{{ $blocked->updated_at }}" disabled>
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
                                        value="{{ $blocked->deleted_at }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-danger"
                                        onclick="event.preventDefault(); document.getElementById('deleteForm').submit();">
                                        Удалить
                                    </a>
                                    <button type="submit" class="btn btn-primary float-right">Обновить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form id="deleteForm" class="d-none"
                        action="{{ route('admin.agents.destroy', ['agent' => $blocked->id]) }}" method="POST">
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
