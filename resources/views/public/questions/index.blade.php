@extends('public.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')

@section('hero')
    <x-ui.page-header title="Вопросы" :items="[['Главная', route('articles.index')], ['Вопросы', null]]" />
@endsection

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('questions.index') }}" role="search" aria-label="Поиск вопросов" class="flex w-full gap-2 sm:max-w-[420px]">
            <label for="q-search" class="sr-only">Поиск вопросов</label>
            <input id="q-search" name="text" type="search" value="{{ request('text') }}" placeholder="Найти ответ..." class="field">
            <x-ui.button variant="dark" class="!h-10 !py-0">Найти</x-ui.button>
        </form>
        <x-ui.button variant="accent" :href="route('questions.create')" class="!h-10 !py-0">Задать вопрос</x-ui.button>
    </div>
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
