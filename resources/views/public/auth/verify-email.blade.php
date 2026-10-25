@extends('public.layouts.app')
@section('title', 'Подтверждение Email | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')

@section('hero')
    <x-ui.page-header title="Подтверждение Email" :items="[ ['Подтверждение Email', null]]" />
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 rounded bg-[#d4edda] px-5 py-3 text-[#155724]" role="alert">На ваш адрес электронной почты отправлена новая ссылка для подтверждения.</div>
        @endif
        <p>Спасибо за регистрацию! Прежде чем начать, пожалуйста, подтвердите свой адрес электронной почты, перейдя по ссылке, которую мы только что отправили вам на почту. Если вы не получили письмо, мы с удовольствием отправим вам еще одно.</p>
        <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
            @csrf
            <button type="submit" class="font-bold text-black hover:text-link">Нажмите здесь</button> для отправки новой ссылки.
        </form>
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'tags'])
@endsection
