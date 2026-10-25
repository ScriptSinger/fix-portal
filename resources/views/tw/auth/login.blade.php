@extends('tw.layouts.auth')
@section('title', 'Вход | ' . config('app.name', 'Ufamasters'))
@section('content')
    <x-tw.auth-alert />
    @if (session('status'))
        <p role="alert" class="mb-6 text-center">{{ session('status') }}</p>
    @else
        <p class="mb-6 text-center">Войдите, чтобы начать сеанс</p>
    @endif
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <x-tw.auth-field name="email" type="email" placeholder="Email" icon="envelope" />
        <x-tw.auth-field name="password" type="password" placeholder="Password" icon="lock" />
        <x-tw.auth-button>Войти</x-tw.auth-button>
    </form>
    <p class="mb-1 mt-4"><a href="{{ route('password.request') }}" class="text-[#007bff] hover:underline">Забыли пароль?</a></p>
    <p class="mb-0"><a href="{{ route('register') }}" class="text-[#007bff] hover:underline">Зарегистрировать новый аккаунт</a></p>
@endsection
