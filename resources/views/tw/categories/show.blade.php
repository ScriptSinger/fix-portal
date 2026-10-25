@extends('tw.layouts.app')
@section('title', "$category->title | " . config('app.name', 'Ufamasters'))
@section('description', $category->description ?? 'Категория статей: ' . $category->title)
@section('canonical', route('categories.show', $category->slug))
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.page-header :title="$category->title" :items="[
        ['Главная', route('articles.index')],
        ['Категории', route('categories.index')],
        [$category->title, null],
    ]" />
@endsection

@section('content')
    <x-tw.post-list :posts="$posts" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
