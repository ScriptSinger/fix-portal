@extends('public.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')

@section('hero')
    <x-ui.page-header title="Вопросы" :items="[['Вопросы', null]]">
        <x-slot:actions><x-ui.button variant="accent" size="lg" :href="route('questions.create')" class="max-sm:w-full">Задать вопрос</x-ui.button></x-slot:actions>
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => null])
    @include('public.partials.ask-cta')
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
