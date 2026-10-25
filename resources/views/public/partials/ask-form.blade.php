{{-- Форма «Задать вопрос» над списком вопросов (общий список и страницы техники). $selected: тип техники, выбранный заранее --}}
@props([])
<div id="ask" class="mb-6 scroll-mt-24">
    @auth('web')
        <x-questions.form collapsed :action="route('questions.store')" :appliances="$appliances" :selected="$selected ?? null" />
    @else
        <p class="m-0 rounded-2xl bg-white px-5 py-4 shadow-sm">Чтобы задать вопрос, необходимо
            <a class="font-bold text-black underline" href="{{ route('register') }}">зарегистрироваться</a>
            или <a class="font-bold text-black underline" href="{{ route('login') }}">войти</a></p>
    @endauth
</div>
