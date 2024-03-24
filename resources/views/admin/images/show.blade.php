@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Изображения</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.images.index') }}">Изображения</a></li>
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
                        Количество изображений: {{ count($images) }}
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($images as $image)
                                <div class="col-sm-2">
                                    <a href="{{ $image->url }}" data-toggle="lightbox"
                                        data-footer="{{ $image->mime }} {{ $image->size_mb }}"
                                        data-title="{{ $image->url }}" data-gallery="gallery">
                                        <img src="{{ $image->url }}" class="img-fluid img-thumbnail rounded">
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
    <script src="{{ asset('assets/admin/js/custom/ekko-lightbox/images.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom/filterizr/images.js') }}"></script>
@endpush
