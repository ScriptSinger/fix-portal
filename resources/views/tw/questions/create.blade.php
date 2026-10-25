@extends('tw.layouts.app')
@section('title', 'Создать вопрос | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')

@section('hero')
    <x-tw.page-header title="Создать вопрос" :items="[
        ['Главная', route('articles.index')],
        ['Вопросы', route('questions.index')],
        ['Создать вопрос', null],
    ]" />
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <form method="POST" action="{{ route('questions.store') }}">
            @csrf
            
            <x-tw.field name="title" label="Название вопроса" :value="null" placeholder="Введите бренд и модель прибора (например, Samsung RL39)" required />
            <x-tw.select name="appliance_id" label="Тип прибора" :options="$appliances" :value="null" />
            <div class="mb-6">
                <span class="mb-2 block">Неисправность</span>
                <x-tw.editor name="description" value=""
                    :upload-url="route('api.users.images.upload')" :destroy-url="route('api.users.images.destroy', ['image' => ':id'])" />
                @error('description')<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-tw.button variant="dark">Отправить</x-tw.button>
        </form>
    </div>
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
