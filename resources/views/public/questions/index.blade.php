@extends('public.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')
@section('canonical', route('questions.index'))
@php
    $isPaginated = request('page', 1) > 1;
    $hasFilter = request()->hasAny(['text', 'status']);
@endphp
@section('robots', $isPaginated || $hasFilter ? 'noindex, follow' : 'index, follow')

@section('hero')
    <x-ui.page-header title="Вопросы" :items="[['Вопросы', null]]" />
@endsection

@section('content')
    @include('public.partials.ask-form')
    @include('public.partials.status-filter')
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => null])
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
