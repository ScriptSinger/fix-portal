@extends('public.layouts.app')
@section('title', 'Согласие на обработку персональных данных | ' . config('app.name', 'Ufamasters'))
@section('description', 'Согласие пользователя на обработку персональных данных при регистрации на сайте.')
@section('robots', 'noindex, follow')
@section('no-sidebar', true)

@section('hero')
    <x-ui.page-header title="Согласие на обработку персональных данных" :items="[['Статьи', route('articles.index')], ['Согласие на обработку данных', null]]" />
@endsection

@section('content')
    <x-ui.card class="prose prose-lg max-w-3xl">
        @include('public.partials.consent-text')
    </x-ui.card>
@endsection
