@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Статистика</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Статистика</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">

                    @include('admin.statistics.widgets.posts_count')
                    @include('admin.statistics.widgets.questions_count')
                    @include('admin.statistics.widgets.firmwares_count')
                    @include('admin.statistics.widgets.firmwares_files_count')
                    @include('admin.statistics.widgets.users_images_count')

                    @include('admin.statistics.widgets.user_registrations')
                    @include('admin.statistics.widgets.comments_count')
                    @include('admin.statistics.widgets.replies_count')


                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/statictic.js') }}"></script>
@endpush
