@extends('tw.layouts.app')
@section('title', 'Категории статей | ' . config('app.name', 'Ufamasters'))
@section('description', 'Список категорий статей по ремонту бытовой техники.')
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.page-header title="Категории статей" :items="[['Главная', route('articles.index')], ['Категории', null]]" />
@endsection

@section('content')
    <x-tw.tag-cloud :items="$categories->map(fn ($c) => [$c->title, route('categories.show', ['category' => $c->slug])])->all()" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
