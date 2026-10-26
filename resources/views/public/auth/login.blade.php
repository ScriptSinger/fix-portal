@extends('public.layouts.app')
@section('title', 'Вход | ' . config('app.name', 'Ufamasters'))
@section('description', 'Вход в личный кабинет UfaMasters: вопросы, комментарии и скачивание прошивок.')
@section('robots', 'noindex, nofollow')
@section('bare', true)
@section('slim-footer', true)
@section('main-pt', 'pt-0 sm:pt-12')

@section('content')
    <x-ui.auth-shell title="Вход" eyebrow="Право на ремонт" headline="Вещи должны чиниться, а не выбрасываться." :points="['Доступ к инструкциям и схемам', 'Доступ к прошивкам', 'Свободный выбор мастера', 'Ремонт по разумной цене']">
        @if (session('status'))
            <x-ui.notice type="success">{{ session('status') }}</x-ui.notice>
        @endif
        <x-ui.social-login />
        <form action="{{ route('login') }}" method="POST" x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <x-ui.field name="email" type="email" label="Email" placeholder="name@example.com" autocomplete="email" required large />
            <x-ui.password name="password" label="Пароль">
                <x-slot:aside><a href="{{ route('password.request') }}" class="-my-2.5 inline-block py-2.5 text-[13px] font-bold">Забыли пароль?</a></x-slot:aside>
            </x-ui.password>
            <x-ui.button variant="dark" class="w-full !py-0 h-12" x-bind:disabled="busy"><span x-text="busy ? 'Входим…' : 'Войти'">Войти</span></x-ui.button>
        </form>
        <p class="mb-0 mt-8 border-t border-[#e5e5e5] pt-6">Нет аккаунта? <a href="{{ route('register') }}" class="font-bold text-black underline">Зарегистрироваться</a></p>
        <p class="mb-0 mt-4 text-[13px] leading-5 text-subtle">Вход через Google отключён. Раньше входили через Google? Нажмите «Забыли пароль?» и задайте пароль для входа.</p>
    </x-ui.auth-shell>
@endsection
