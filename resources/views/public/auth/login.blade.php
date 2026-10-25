@extends('public.layouts.app')
@section('title', 'Вход | ' . config('app.name', 'Ufamasters'))
@section('description', 'Вход в личный кабинет UfaMasters: вопросы, комментарии и скачивание прошивок.')
@section('robots', 'noindex, nofollow')
@section('no-sidebar', true)

@section('hero')
    <x-ui.page-header title="Вход" :items="[['Главная', route('articles.index')], ['Вход', null]]" />
@endsection

@section('content')
    <x-ui.auth-shell>
        @if (session('status'))
            <x-ui.notice type="success">{{ session('status') }}</x-ui.notice>
        @endif
        <x-ui.notice>
            <strong>Внимание:</strong> вход через Google отключён. Если вы раньше регистрировались через Google, нажмите
            <a href="{{ route('password.request') }}" class="keep font-bold underline">«Забыли пароль?»</a>, чтобы задать пароль для входа.
        </x-ui.notice>
        <form action="{{ route('login') }}" method="POST" x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <x-ui.field name="email" type="email" label="Email" placeholder="name@example.com" autocomplete="email" required />
            <x-ui.password name="password" label="Пароль" />
            <x-ui.button variant="dark" class="w-full" x-bind:disabled="busy"><span x-text="busy ? 'Входим…' : 'Войти'">Войти</span></x-ui.button>
        </form>
        <p class="mb-2 mt-6"><a href="{{ route('password.request') }}">Забыли пароль?</a></p>
        <p class="mb-0">Нет аккаунта? <a href="{{ route('register') }}" class="font-bold text-black">Зарегистрироваться</a></p>
    </x-ui.auth-shell>
@endsection
