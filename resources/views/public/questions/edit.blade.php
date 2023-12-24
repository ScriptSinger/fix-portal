@extends('public.layouts.bar')
@section('title', 'Редактировать вопрос | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>
                    </h2>
                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Главная</a></li>
                        <li class="breadcrumb-item active">Редактировать вопрос</li>
                    </ol>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end container -->
    </div><!-- end page-title -->
@endsection
@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.includes.sidebar_widgets.prime_posts')
        @include('public.layouts.includes.sidebar_widgets.advertising')
        @include('public.layouts.includes.sidebar_widgets.prime_categories')

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
                        <label for="exampleSelect">Прибор</label>
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
                        <label for="description">Неисправность</label>
                        <textarea id="description" name="description" class="p-1 px-2 w-100">{{ $question->description }}</textarea>
                        <span class="form-control-feedback">
                            @error('description')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="custom-file w-100 mb-4">

                        <label class="custom-file-control" for="photo"></label>
                        <input type="file" class="custom-file-input w-100" id="photo" name="photos[]" multiple>
                        <div class="form-control-feedback">
                            @error('photos')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <button role="button" type="submit" class="btn mt-4">Отправить</button>
                </form>
            </div>
        </div>
    </div><!-- end page-wrapper -->
    </div><!-- end col -->

@endsection
