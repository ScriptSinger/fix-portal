@extends('public.layouts.app')
@section('title', "$tag->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Статьи по тегу: ' . $tag->title . '.')
@section('canonical', route('tags.show', $tag->slug))
@section('robots', request('page', 1) > 1 ? 'noindex, follow' : 'index, follow')

@section('hero')
    <x-ui.page-header :title="$tag->title" :items="[
        ['Теги', route('tags.index')],
        [$tag->title, null],
    ]" />
@endsection

@section('content')
    <x-ui.post-list :posts="$posts" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'tags'])
@endsection
