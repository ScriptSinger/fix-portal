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
                            <li class="breadcrumb-item"><a href="{{ route('admin.tags.index') }}">Метки</a></li>
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
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.tags.update', ['tag' => $tag->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label>Название</label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror" value="{{ $tag->title }}">
                                <span class="error invalid-feedback">
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
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
