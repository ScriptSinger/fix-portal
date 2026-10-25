@extends('public.layouts.app')
@section('title', 'Создать вопрос | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')

@section('hero')
    <x-ui.page-header title="Создать вопрос" :items="[
        ['Вопросы', route('questions.index')],
        ['Создать вопрос', null],
    ]" />
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <x-questions.form :action="route('questions.store')" :appliances="$appliances" />
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
