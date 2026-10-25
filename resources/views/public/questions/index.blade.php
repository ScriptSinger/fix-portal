@extends('public.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')

@section('hero')
    <x-ui.page-header title="Вопросы" :items="[['Вопросы', null]]" />
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
