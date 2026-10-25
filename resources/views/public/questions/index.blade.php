@extends('public.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')

@section('hero')
    <x-ui.page-header title="Вопросы" :items="[['Главная', route('articles.index')], ['Вопросы', null]]" />
@endsection

@section('content')
    <div class="mb-6 flex justify-end">
        <x-ui.button variant="accent" :href="route('questions.create')" class="!h-10 !py-0 max-sm:w-full">Задать вопрос</x-ui.button>
    </div>
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => null])
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
