@extends('tw.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')

@section('hero')
    <x-tw.hero :customization="$customization" search-title="Поиск вопросов"
        search-text="Возможно, уже есть ответы на ваш вопрос. Попробуйте воспользоваться поиском"
        placeholder="Найти ответ..." label="Поиск вопросов" :action="route('questions.index')" />
@endsection

@section('content')
    <x-tw.question-list :questions="$questions" />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
