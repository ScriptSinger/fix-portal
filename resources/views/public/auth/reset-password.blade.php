@extends('public.layouts.app')
@section('title', 'Новый пароль | ' . config('app.name', 'Ufamasters'))
@section('description', 'Установка нового пароля на UfaMasters.')
@section('robots', 'noindex, nofollow')
@section('no-sidebar', true)

@section('hero')
    <x-ui.page-header title="Новый пароль" :items="[['Главная', route('articles.index')], ['Новый пароль', null]]" />
@endsection

@section('content')
    <x-ui.auth-shell>
        @if (session('status'))
            <x-ui.notice type="success">{{ session('status') }}</x-ui.notice>
        @else
            <p class="mb-6">Вы в одном шаге от нового пароля. Придумайте его и повторите.</p>
            <form action="{{ route('password.store') }}" method="POST" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <input type="hidden" name="email" value="{{ $request->input('email') }}">
                <x-ui.password name="password" label="Новый пароль" autocomplete="new-password" />
                <x-ui.password name="password_confirmation" label="Повторите пароль" autocomplete="new-password" />
                <x-ui.button variant="dark" class="w-full" x-bind:disabled="busy"><span x-text="busy ? 'Сохраняем…' : 'Сохранить пароль'">Сохранить пароль</span></x-ui.button>
            </form>
        @endif
        <p class="mb-0 mt-6"><a href="{{ route('login') }}" class="font-bold text-black">Войти</a></p>
    </x-ui.auth-shell>
@endsection
