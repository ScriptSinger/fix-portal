@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Кастомизация</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Кастомизация</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <form method="POST" enctype="multipart/form-data" action="{{ route('custom.update') }}" id="quickForm"
                novalidate="novalidate">
                @csrf
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                                class="fas fa-expand"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputTitle">Заголовок</label>
                                        <input type="text" name="title"
                                            class="form-control @error('title') is-invalid @enderror" id="InputTitle"
                                            placeholder="Добавить заголовок" aria-describedby="InputTitle-error"
                                            aria-invalid="true" required value="{{ optional($customization)->title }}">
                                        <span id="InputTitle-error" class="error invalid-feedback">
                                            @error('title')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="InputTextArea1">Содержание</label>
                                        <textarea id="InputTextArea1" name="description" class="form-control @error('description') is-invalid @enderror"
                                            rows="7" placeholder="Рекомендуемое количество символов: 216">{{ optional($customization)->description }}</textarea>
                                        <span id="InputTextArea1-error" class="error invalid-feedback">
                                            @error('description')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="InputTextArea2">Copyright</label>
                                        <textarea id="InputTextArea2" name="copyright" class="form-control @error('copyright') is-invalid @enderror"
                                            rows="3" placeholder="Enter ...">{{ optional($customization)->copyright }}</textarea>
                                        <span id="InputTextArea2-error" class="error invalid-feedback">
                                            @error('copyright')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Favicon</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputFile"></label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="favicon" id="InputFile"
                                                    class="custom-file-input  @error('favicon') is-invalid @enderror">
                                                <label class="custom-file-label" for="favicon">Choose file</label>
                                            </div>
                                        </div>
                                        <span id="InputFile-error" class="error invalid-feedback" style="display: inline;">
                                            @error('favicon')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div style="max-width: 100px"><img class="img-thumbnail"
                                            src="{{ optional($customization)->getImage('favicon') }}" alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Логотип</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputFile">Разрешение: <code>200x50px</code></label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="logo" id="InputFile"
                                                    class="custom-file-input  @error('logo') is-invalid @enderror">
                                                <label class="custom-file-label" for="logo">Choose file</label>
                                            </div>
                                        </div>

                                        <span id="InputFile-error" class="error invalid-feedback"
                                            style="display: inline;">
                                            @error('logo')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div style="max-width: 100px"><img class="img-thumbnail"
                                            src="{{ optional($customization)->getImage('logo') }}" alt="">
                                    </div>
                                </div>
                            </div>

                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Баннер</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputFile">
                                            Разрешение: <code>1922x1044px</code>
                                        </label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="banner" id="InputFile"
                                                    class="custom-file-input  @error('banner') is-invalid @enderror">
                                                <label class="custom-file-label" for="banner">Choose file</label>
                                            </div>
                                        </div>
                                        <span id="InputFile-error" class="error invalid-feedback"
                                            style="display: inline;">
                                            @error('banner')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div style="max-width: 100px"><img class="img-thumbnail"
                                            src="{{ optional($customization)->getImage('banner') }}" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </form>
        </section>
    </div>
@endsection
