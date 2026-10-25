@extends('tw.layouts.app')
@section('title', "$tag->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Статьи по тегу: ' . $tag->title . '.')
@section('canonical', route('tags.show', $tag->slug))

@section('hero')
    <x-tw.page-header :title="$tag->title" :items="[
        ['Главная', route('articles.index')],
        ['Теги', route('tags.index')],
        [$tag->title, null],
    ]" />
@endsection

@section('content')
    <x-tw.post-list :posts="$posts" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar', ['variant' => 'tags'])
@endsection
