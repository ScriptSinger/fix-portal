@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Файлы</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.files.index') }}">Файлы</a></li>
                            <li class="breadcrumb-item active">Галерея</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        Количество файлов: {{ count($files) }}
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($files as $file)
                                <div class="col-sm-2">
                                    <a href="{{ $file->url }}" data-toggle="lightbox"
                                        data-footer="{{ $file->mime }} {{ $file->size_mb }}"
                                        data-title="{{ $file->url }}" data-gallery="gallery">
                                        <img src="{{ $file->url }}" class="img-fluid img-thumbnail rounded">
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="filter-container">
                            <div class="filter-item"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/ekko-lightbox/files.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom/filterizr/files.js') }}"></script>
@endpush
