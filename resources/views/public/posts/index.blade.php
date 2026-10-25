@extends('public.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@php
    $isPaginated = request('page', 1) > 1;
    $hasFilter = request()->hasAny(['text']);
@endphp
@section('robots', $isPaginated || $hasFilter ? 'noindex, follow' : 'index, follow')

@section('hero')
    <x-ui.page-header title="Статьи" :items="[['Статьи', null]]" />
@endsection

@section('sidebar-left')
    @include('public.partials.category-nav', ['active' => null])
@endsection

@section('content')
    @if ($hasFilter)
        <p class="mb-6 text-sm">Результаты поиска: <strong>«{{ request('text') }}»</strong>. <a href="{{ route('articles.index') }}" class="font-bold text-black underline">Сбросить</a></p>
    @endif
    <x-ui.post-list :posts="$posts" show-category />
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
