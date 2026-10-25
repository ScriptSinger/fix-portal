@extends('public.layouts.app')
@section('title', 'Восстановление пароля | ' . config('app.name', 'Ufamasters'))
@section('description', 'Восстановление пароля на UfaMasters: пришлём ссылку для нового пароля на вашу почту.')
@section('robots', 'noindex, nofollow')
@section('bare', true)
@section('slim-footer', true)
@section('main-pt', 'pt-0 sm:pt-12')


@section('content')
    <x-ui.auth-shell title="Восстановление пароля" headline="Забыли пароль? Это быстро." :points="['Пришлём ссылку на вашу почту', 'Ссылка действует ограниченное время', 'Старый пароль перестанет работать']">
        @if (session('status'))
            <x-ui.notice type="success">{{ session('status') }}</x-ui.notice>
        @else
            <form action="{{ route('password.email') }}" method="POST" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <x-ui.field name="email" type="email" label="Email" placeholder="name@example.com" autocomplete="email" required large />
                <x-ui.button variant="dark" class="h-12 w-full !py-0" x-bind:disabled="busy"><span x-text="busy ? 'Отправляем…' : 'Получить ссылку'">Получить ссылку</span></x-ui.button>
            </form>
        @endif
        <p class="mb-0 mt-6"><a href="{{ route('login') }}" class="font-bold text-black">Вернуться ко входу</a></p>
    </x-ui.auth-shell>
@endsection
