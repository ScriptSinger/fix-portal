@extends('public.layouts.app')
@section('title', "$appliance->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Вопросы и ответы по технике: ' . $appliance->title . '. Решения, советы и опыт пользователей.')

@section('hero')
    <x-ui.page-header :title="$appliance->title" :items="[
        ['Вопросы', route('questions.index')],
        [$appliance->title, null],
    ]">
        <x-slot:actions><x-ui.button variant="accent" size="lg" :href="route('questions.create')" class="max-sm:w-full">Задать вопрос</x-ui.button></x-slot:actions>
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => $appliance->slug])
    @include('public.partials.ask-cta')
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
