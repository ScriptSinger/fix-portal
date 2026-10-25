@extends('public.layouts.app')
@section('title', 'Редактировать вопрос | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')

@section('hero')
    <x-ui.page-header title="Редактировать вопрос" :items="[
        ['Вопросы', route('questions.index')],
        ['Редактировать вопрос', null],
    ]" />
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <x-questions.form :action="route('questions.update', ['question' => $question->slug])" method="PUT" :question="$question" :appliances="$appliances" />
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
