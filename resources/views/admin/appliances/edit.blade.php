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
                            <li class="breadcrumb-item"><a href="{{ route('appliances.index') }}">Приборы</a></li>
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
                    <form method="POST" action="{{ route('appliances.update', ['appliance' => $appliance->id]) }}"
                        id="quickForm" novalidate="novalidate">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="InputText">Название</label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror" id="InputTextAppliance"
                                    value="{{ $appliance->title }}" aria-describedby="InputText-error" aria-invalid="true">
                                <span id="InputText-error" class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="InputText">Slug</label>
                                <input type="text" class="form-control" id="InputTextAppliance" disabled
                                    value="{{ $appliance->slug }}">
                            </div>

                            <div class="form-group">
                                <label for="InputText">Создано</label>
                                <input type="text" class="form-control" id="InputTextAppliance" disabled
                                    value="{{ $appliance->dateAsCarbon->format('d-m-Y H:i:s') }}">
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-danger"
                                        onclick="event.preventDefault(); confirm('Подтвердите удаление'); document.getElementById('deleteForm').submit();">
                                        Удалить
                                    </a>

                                    <button type="submit" class="btn btn-primary float-right">Редактировать</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form id="deleteForm" class="d-none"
                        action="{{ route('appliances.destroy', ['appliance' => $appliance->id]) }}" method="POST">
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
