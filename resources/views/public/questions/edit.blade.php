@extends('public.layouts.bar')
@section('title', 'Редактировать вопрос | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>
                    </h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Главная</a></li>
                        <li class="breadcrumb-item active">Редактировать вопрос</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.prime_posts')
        @include('public.layouts.widgets.sidebar.advertising')
        @include('public.layouts.widgets.sidebar.prime_categories')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <form method="POST" enctype="multipart/form-data"
                    action="{{ route('questions.update', ['question' => $question->slug]) }}"
                    class="form-wrapper has-danger">
                    @csrf
                    @method('PUT')
                    <h4>Редактировать вопрос</h4>
                    <div class="form-group">
                        <label for="title">Модель</label>
                        <input class="p-1 px-2 w-100 @error('title') is-invalid  @enderror" type="text" name="title"
                            id="title" value="{{ $question->title }}">
                        <span class="form-control-feedback">
                            @error('title')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="exampleSelect">Тип прибора</label>
                        <select class="custom-select w-100" id="exampleSelect" name="appliance_id">
                            @foreach ($appliances as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        <span class="form-control-feedback">
                            @error('appliance')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="form-group">
                        <label>Неисправность</label>
                        <textarea class="summernote" name="description" data-upload-url="{{ route('api.summernote.upload') }}">{{ $question->description }}</textarea>
                        <span class="form-control-feedback">
                            @error('description')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <button role="button" type="submit" class="btn btn-dark">Обновить</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/summernote/basic.js') }}"></script>
@endpush
