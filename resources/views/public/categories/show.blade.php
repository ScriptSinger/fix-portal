@extends('public.layouts.app')
@section('title', "$category->title | " . config('app.name', 'Ufamasters'))
@section('description', $category->description ?? 'Категория статей: ' . $category->title)
@section('canonical', route('categories.show', $category->slug))
@section('robots', request('page', 1) > 1 ? 'noindex, follow' : 'index, follow')

@section('hero')
    <x-ui.page-header :title="$category->title" :items="[
        [$category->title, null],
    ]" />
@endsection

@section('content')
    <x-ui.post-list :posts="$posts" />
@endsection

@section('sidebar-left')
    @include('public.partials.category-nav', ['active' => $category->slug])
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
