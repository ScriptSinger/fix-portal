@extends('public.layouts.app')
@section('title', "$appliance->title | " . config('app.name', 'Ufamasters'))
@section('description', 'Вопросы и ответы по технике: ' . $appliance->title . '. Решения, советы и опыт пользователей.')
@section('canonical', route('public.appliances.show', ['appliance' => $appliance->slug]))
@php
    $isPaginated = request('page', 1) > 1;
    $hasFilter = request()->hasAny(['status']);
@endphp
@section('robots', $isPaginated || $hasFilter ? 'noindex, follow' : 'index, follow')

@section('hero')
    <x-ui.page-header :title="$appliance->title" :items="[
        ['Вопросы', route('questions.index')],
        [$appliance->title, null],
    ]">
    </x-ui.page-header>
@endsection

@section('content')
    @include('public.partials.ask-form', ['selected' => $appliance->id])
    @include('public.partials.status-filter')
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => $appliance->slug])
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
