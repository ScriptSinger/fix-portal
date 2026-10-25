@extends('tw.layouts.app')
@section('title', 'Согласие на обработку персональных данных | ' . config('app.name', 'Ufamasters'))
@section('description', 'Согласие пользователя на обработку персональных данных при регистрации на сайте.')
@section('robots', 'noindex, follow')
@section('no-sidebar', true)

@section('hero')
    <x-tw.page-header title="Согласие на обработку персональных данных" :items="[['Главная', route('articles.index')], ['Согласие на обработку данных', null]]" />
@endsection

@section('content')
    <x-tw.card class="prose prose-lg max-w-3xl">
        @include('tw.partials.consent-text')
    </x-tw.card>
@endsection
