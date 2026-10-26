@extends('public.layouts.app')
@section('title', 'Новый пароль | ' . config('app.name', 'Ufamasters'))
@section('description', 'Установка нового пароля на UfaMasters.')
@section('robots', 'noindex, nofollow')
@section('bare', true)
@section('slim-footer', true)
@section('main-pt', 'pt-0 sm:pt-12')


@section('content')
    <x-ui.auth-shell title="Новый пароль" headline="Остался один шаг до нового пароля." :points="['Пришлём ссылку на вашу почту', 'Ссылка действует ограниченное время', 'Старый пароль перестанет работать']">
        @if (session('status'))
            <x-ui.notice type="success">{{ session('status') }}</x-ui.notice>
        @else
            <form action="{{ route('password.store') }}" method="POST" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <input type="hidden" name="email" value="{{ $request->input('email') }}">
                <x-ui.password name="password" label="Новый пароль" autocomplete="new-password" />
                <x-ui.password name="password_confirmation" label="Повторите пароль" autocomplete="new-password" />
                <x-ui.button variant="dark" class="h-12 w-full !py-0" x-bind:disabled="busy"><span x-text="busy ? 'Сохраняем…' : 'Сохранить пароль'">Сохранить пароль</span></x-ui.button>
            </form>
        @endif
        <p class="mb-0 mt-6"><a href="{{ route('login') }}" class="font-bold text-black">Войти</a></p>
    </x-ui.auth-shell>
@endsection
