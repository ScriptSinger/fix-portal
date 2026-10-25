@extends('tw.layouts.auth')
@section('title', 'Новый пароль | ' . config('app.name', 'Ufamasters'))
@section('content')
    @if (session('status'))
        <div class="rounded bg-[#d4edda] px-5 py-3 text-[#155724]" role="alert">{{ session('status') }}</div>
    @else
        <p class="mb-6 text-center">Вы всего в одном шаге от нового пароля, восстановите его прямо сейчас.</p>
        <form action="{{ route('password.store') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" name="email" value="{{ $request->input('email') }}">
            <x-tw.auth-field name="password" type="password" placeholder="Password" icon="lock" autocomplete="new-password" />
            <x-tw.auth-field name="password_confirmation" type="password" placeholder="Confirm Password" icon="lock" />
            <x-tw.auth-button>Изменить пароль</x-tw.auth-button>
        </form>
        <p class="mb-0 mt-4"><a href="{{ route('login') }}" class="text-[#007bff] hover:underline">Войти</a></p>
    @endif
@endsection
