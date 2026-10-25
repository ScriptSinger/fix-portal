@extends('public.layouts.app')
@section('title', 'Восстановление пароля | ' . config('app.name', 'Ufamasters'))
@section('description', 'Восстановление пароля на UfaMasters: пришлём ссылку для нового пароля на вашу почту.')
@section('robots', 'noindex, nofollow')
@section('no-sidebar', true)

@section('hero')
    <x-ui.page-header title="Восстановление пароля" :items="[['Главная', route('articles.index')], ['Вход', route('login')], ['Восстановление пароля', null]]" />
@endsection

@section('content')
    <x-ui.auth-shell>
        @if (session('status'))
            <x-ui.notice type="success">{{ session('status') }}</x-ui.notice>
        @else
            <p class="mb-6">Забыли пароль? Укажите почту, и мы пришлём ссылку для нового пароля.</p>
            <form action="{{ route('password.email') }}" method="POST" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <x-ui.field name="email" type="email" label="Email" placeholder="name@example.com" autocomplete="email" required />
                <x-ui.button variant="dark" class="w-full" x-bind:disabled="busy"><span x-text="busy ? 'Отправляем…' : 'Получить ссылку'">Получить ссылку</span></x-ui.button>
            </form>
        @endif
        <p class="mb-0 mt-6"><a href="{{ route('login') }}" class="font-bold text-black">Вернуться ко входу</a></p>
    </x-ui.auth-shell>
@endsection
