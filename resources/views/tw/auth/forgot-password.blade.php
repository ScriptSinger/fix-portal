@extends('tw.layouts.auth')
@section('title', 'Восстановление пароля | ' . config('app.name', 'Ufamasters'))
@section('content')
    @if (session('status'))
        <p>{{ session('status') }}</p>
    @else
        <p class="mb-6 text-center">Забыли пароль? Здесь вы можете легко восстановить его.</p>
        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <x-tw.auth-field name="email" type="email" placeholder="Email" icon="envelope" />
            <x-tw.auth-button>Запросить новый пароль</x-tw.auth-button>
        </form>
        <p class="mb-1 mt-4"><a href="{{ route('login') }}" class="text-[#007bff] hover:underline">Вход</a></p>
        <p class="mb-0"><a href="{{ route('register') }}" class="text-[#007bff] hover:underline">Зарегистрировать новый аккаунт</a></p>
    @endif
@endsection
