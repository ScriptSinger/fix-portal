@extends('public.layouts.app')
@section('title', 'Теги статей | ' . config('app.name', 'Ufamasters'))
@section('description', 'Список тегов статей по ремонту бытовой техники.')

@section('hero')
    <x-ui.page-header title="Теги статей" :items="[['Главная', route('articles.index')], ['Теги', null]]" />
@endsection

@section('content')
    <x-ui.tag-cloud :items="$tags->map(fn ($t) => [$t->title, route('tags.show', ['tag' => $t->slug])])->all()" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'tags'])
@endsection
