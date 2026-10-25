@extends('tw.layouts.app')
@section('title', 'Теги статей | ' . config('app.name', 'Ufamasters'))
@section('description', 'Список тегов статей по ремонту бытовой техники.')
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.page-header title="Теги статей" :items="[['Главная', route('articles.index')], ['Теги', null]]" />
@endsection

@section('content')
    <x-tw.tag-cloud :items="$tags->map(fn ($t) => [$t->title, route('tags.show', ['tag' => $t->slug])])->all()" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar', ['variant' => 'tags'])
@endsection
