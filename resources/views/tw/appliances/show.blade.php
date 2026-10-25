@extends('tw.layouts.app')
@section('title', "$appliance->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Вопросы и ответы по технике: ' . $appliance->title . '. Решения, советы и опыт пользователей.')

@section('hero')
    <x-tw.page-header :title="$appliance->title" :items="[
        ['Главная', route('articles.index')],
        ['Вопросы', route('questions.index')],
        [$appliance->title, null],
    ]" />
@endsection

@section('content')
    <x-tw.question-list :questions="$questions" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
