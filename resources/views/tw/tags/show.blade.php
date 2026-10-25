@extends('tw.layouts.app')
@section('title', "$tag->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Статьи по тегу: ' . $tag->title . '.')
@section('canonical', route('tags.show', $tag->slug))
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.page-header :title="'#' . $tag->title" eyebrow="Тег" :items="[
        ['Статьи', route('articles.index')],
        ['Теги', route('tags.index')],
        [$tag->title, null],
    ]" />
@endsection

@section('content')
    <x-tw.post-grid :posts="$posts" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
