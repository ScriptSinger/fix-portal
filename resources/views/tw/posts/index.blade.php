@extends('tw.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.hero :customization="$customization" search-title="Поиск статей" search-text="Возможно, нужная информация уже есть в статьях. Попробуйте поиск"
        placeholder="Что вы хотели бы узнать?" label="Поиск статей"
        :action="request()->is('pilot/*') ? route('pilot.articles') : route('articles.index')" />
@endsection

@section('content')
    <x-tw.post-list :posts="$posts" show-category />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
