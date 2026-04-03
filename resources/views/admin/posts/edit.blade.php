@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Редактировать статью</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Статьи</a></li>
                            <li class="breadcrumb-item active">Редактировать статью</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <form method="POST" enctype="multipart/form-data"
                action="{{ route('admin.posts.update', ['post' => $post->id]) }}" id="quickForm" novalidate="novalidate">
                @csrf
                @method('PUT')
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <div class="user-block">
                                        <img class="img-circle" src="{{ asset('assets/front/images/avatar.png') }}"
                                            alt="User Image">
                                        <span class="username"><a href="#">{{ $post->administrator->name }}
                                                #
                                                {{ $post->administrator->id }}</a></span>
                                        <span class="description">Shared publicly - 7:30 PM Today</span>
                                    </div>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                                class="fas fa-expand"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Название</label>
                                        <input type="text" name="title"
                                            class="form-control @error('title') is-invalid @enderror"
                                            value="{{ $post->title }}" required>
                                        <span class="error invalid-feedback">
                                            @error('title')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="InputLink">Постоянная ссылка</label>
                                        <input type="text" class="form-control" id="InputLink"
                                            value="{{ $post->slug }}" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Содержание</label>
                                        <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror"
                                            data-routes='{
                                            "upload": "{{ route('api.post.images.upload') }}",
                                            "destroy": "{{ route('api.post.images.destroy', ['image' => ':id']) }}"
                                        }'>{{ $post->content }}</textarea>
                                        <span id="contentError" class="error invalid-feedback">
                                            @error('content')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label>Мета-описание</label>
                                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                            rows="3">{{ $post->description }}</textarea>
                                        <span id="descriptionError" class="error invalid-feedback">
                                            @error('description')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Категории</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputSelect1"></label>
                                        <select class="select2 @error('category_id') is-invalid @enderror" id="InputSelect1"
                                            name="category_id" style="width: 100%;">
                                            @foreach ($categories as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if ($key == $post->category_id) selected @endif>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span id="InputSelect1-error" class="error invalid-feedback">
                                            @error('category_id')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('admin.categories.create') }}" type="submit"
                                        class="btn btn-primary"><i class="fas fa-plus"></i> Добавить</a>
                                </div>
                            </div>
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Метки</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputSelect2"></label>
                                        <select name="tags[]" id="InputSelect2" class="select2" multiple="multiple"
                                            data-placeholder="Выбрать тег" style="width: 100%;">
                                            @foreach ($tags as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (in_array($key, $post->tags->pluck('id')->all())) selected @endif>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('admin.tags.create') }}" type="submit" class="btn btn-primary"><i
                                            class="fas fa-plus"></i> Добавить</a>
                                </div>
                            </div>
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Изображение записи</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3"><img class="img-thumbnail"
                                            src="{{ optional($post->thumbnail)->small }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="thumbnail" id="InputFile"
                                                    class="custom-file-input">
                                                <label class="custom-file-label" for="thumbnail">Choose file</label>
                                            </div>
                                        </div>
                                        <span id="InputFile-error" class="error invalid-feedback"
                                            style="display: inline;">
                                            @error('thumbnail')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
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
                </div>
            </form>
            <form id="deleteForm" class="d-none" action="{{ route('admin.posts.destroy', ['post' => $post->id]) }}"
                method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">
                    Удалить
                </button>
            </form>

            <form method="POST" action="{{ route('admin.posts.cta.upsert', ['post' => $post->id]) }}">
                @csrf
                @method('PUT')
                <div class="card card-outline card-warning mt-4">
                    <div class="card-header">
                        <h3 class="card-title">CTA для коммерческой страницы</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>URL посадочной</label>
                                    <input type="url" name="cta_target_url"
                                        class="form-control @error('cta_target_url') is-invalid @enderror"
                                        placeholder="https://appliance-repair.ru/..."
                                        value="{{ old('cta_target_url', $cta->target_url ?? '') }}">
                                    <span class="error invalid-feedback">
                                        @error('cta_target_url')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Заголовок CTA</label>
                                    <input type="text" name="cta_title"
                                        class="form-control @error('cta_title') is-invalid @enderror"
                                        value="{{ old('cta_title', $cta->title ?? '') }}">
                                    <small class="form-text text-muted">Показывается как заголовок рекламного блока.</small>
                                    <span class="error invalid-feedback">
                                        @error('cta_title')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Текст CTA</label>
                                    <textarea name="cta_text" rows="4" class="form-control @error('cta_text') is-invalid @enderror">{{ old('cta_text', $cta->text ?? '') }}</textarea>
                                    <span class="error invalid-feedback">
                                        @error('cta_text')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Текст ссылки</label>
                                    <input type="text" name="cta_anchor"
                                        class="form-control @error('cta_anchor') is-invalid @enderror"
                                        value="{{ old('cta_anchor', $cta->anchor ?? '') }}">
                                    <small class="form-text text-muted">Например: Вызвать мастера в Уфе.</small>
                                    <span class="error invalid-feedback">
                                        @error('cta_anchor')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Город</label>
                                    <input type="text" name="cta_city"
                                        class="form-control @error('cta_city') is-invalid @enderror"
                                        value="{{ old('cta_city', $cta->city ?? 'ufa') }}">
                                    <span class="error invalid-feedback">
                                        @error('cta_city')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Приоритет</label>
                                    <input type="number" min="0" name="cta_priority"
                                        class="form-control @error('cta_priority') is-invalid @enderror"
                                        value="{{ old('cta_priority', $cta->priority ?? 0) }}">
                                    <span class="error invalid-feedback">
                                        @error('cta_priority')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Бренд</label>
                                    <input type="text" name="cta_brand"
                                        class="form-control @error('cta_brand') is-invalid @enderror"
                                        value="{{ old('cta_brand', $cta->brand ?? '') }}">
                                    <span class="error invalid-feedback">
                                        @error('cta_brand')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Тип техники</label>
                                    <input type="text" name="cta_appliance_type"
                                        class="form-control @error('cta_appliance_type') is-invalid @enderror"
                                        value="{{ old('cta_appliance_type', $cta->appliance_type ?? '') }}">
                                    <span class="error invalid-feedback">
                                        @error('cta_appliance_type')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Проблема</label>
                                    <input type="text" name="cta_problem"
                                        class="form-control @error('cta_problem') is-invalid @enderror"
                                        value="{{ old('cta_problem', $cta->problem ?? '') }}">
                                    <span class="error invalid-feedback">
                                        @error('cta_problem')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Размещение</label>
                                    <select name="cta_placement"
                                        class="form-control @error('cta_placement') is-invalid @enderror">
                                        <option value="end" @selected(old('cta_placement', $cta->placement ?? 'end') === 'end')>В конце статьи</option>
                                        <option value="middle" @selected(old('cta_placement', $cta->placement ?? '') === 'middle')>В середине статьи</option>
                                        <option value="sidebar" @selected(old('cta_placement', $cta->placement ?? '') === 'sidebar')>В сайдбаре</option>
                                    </select>
                                    <span class="error invalid-feedback">
                                        @error('cta_placement')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="cta_is_active" value="0">
                                    <input type="checkbox" name="cta_is_active" value="1"
                                        class="form-check-input" id="ctaIsActiveEdit"
                                        @checked(old('cta_is_active', (int) ($cta->is_active ?? 1)))>
                                    <label class="form-check-label" for="ctaIsActiveEdit">CTA активен</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <div>
                            @if ($cta)
                                <button class="btn btn-danger" type="submit" form="deleteCtaForm">Удалить CTA</button>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-warning">Сохранить CTA</button>
                    </div>
                </div>
            </form>

            @if ($cta)
                <form id="deleteCtaForm" action="{{ route('admin.posts.cta.destroy', ['post' => $post->id]) }}"
                    method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="placement" value="{{ $cta->placement }}">
                </form>
            @endif

        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/summernote/full.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom/summernote/lite.js') }}"></script>
@endpush
