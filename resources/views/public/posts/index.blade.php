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
    <x-ui.hero :customization="$customization" search-title="Поиск статей" search-text="Возможно, нужная информация уже есть в статьях. Попробуйте поиск"
        placeholder="Что вы хотели бы узнать?" label="Поиск статей"
        :action="route('articles.index')" />
@endsection

@section('content')
    <x-ui.post-list :posts="$posts" show-category />
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
