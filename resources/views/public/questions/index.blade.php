@extends('public.layouts.app')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
@section('description', 'Получите советы по решению проблем с бытовой техникой. Присоединяйтесь к обсуждению и делитесь своими вопросами')

@section('hero')
    <x-ui.page-header title="Вопросы" :items="[['Вопросы', null]]" />
@endsection

@section('content')
    <div id="ask" class="mb-6 scroll-mt-24">
        @auth('web')
            <x-questions.form collapsed :action="route('questions.store')" :appliances="$appliances" />
        @else
            <p class="m-0 rounded-2xl bg-white px-5 py-4 shadow-sm">Чтобы задать вопрос, необходимо
                <a class="font-bold text-black underline" href="{{ route('register') }}">зарегистрироваться</a>
                или <a class="font-bold text-black underline" href="{{ route('login') }}">войти</a></p>
        @endauth
    </div>
    <x-ui.question-list :questions="$questions" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => null])
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
