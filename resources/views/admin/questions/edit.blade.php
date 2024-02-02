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
                            <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">Вопросы</a></li>
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
                    <form method="POST" action="{{ route('admin.questions.update', ['question' => $question->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="form-group">
                                <label>Название</label>
                                <input type="text" name="title" value="{{ $question->title }}"
                                    class="form-control @error('title') is-invalid @enderror">
                                <span class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>

                            <div class="form-group">
                                <label>Прибор</label>
                                <select class="custom-select w-100" name="appliance_id">
                                    @foreach ($appliances as $key => $value)
                                        <option value="{{ $key }}"
                                            @if ($key == $question->appliance_id) selected @endif>
                                            {{ $value }}</option>
                                    @endforeach
                                </select>
                                <span class="form-control-feedback">
                                    @error('appliance')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="form-group">
                                <label>Содержание</label>
                                <textarea id="content" data-upload-url="{{ route('api.summernote.upload') }}" name="description"
                                    class="form-control @error('description') is-invalid @enderror" placeholder="Enter ...">{{ $question->description }}</textarea>
                                <span class="error invalid-feedback">
                                    @error('description')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-danger"
                                        onclick="event.preventDefault(); confirm('Подтвердите удаление'); document.getElementById('deleteForm').submit();">
                                        Удалить
                                    </a>

                                    <button type="submit" class="btn btn-primary float-right">Обновить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form id="deleteForm" class="d-none"
                        action="{{ route('admin.questions.destroy', ['question' => $question->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/summernote/content.js') }}"></script>
@endpush
