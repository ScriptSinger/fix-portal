@extends('public.layouts.app')
@section('title', "$appliance->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Вопросы и ответы по технике: ' . $appliance->title . '. Решения, советы и опыт пользователей.')

@section('hero')
    <x-ui.page-header :title="$appliance->title" :items="[
        ['Статьи', route('articles.index')],
        ['Вопросы', route('questions.index')],
        [$appliance->title, null],
    ]" />
@endsection

@section('content')
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => $appliance->slug])
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
