{{-- Форма «Задать вопрос» над списком вопросов (общий список и страницы техники). $selected: тип техники, выбранный заранее --}}
@props([])
<div id="ask" class="mb-6 scroll-mt-24">
    @auth('web')
        <x-questions.form collapsed :action="route('questions.store')" :appliances="$appliances" :selected="$selected ?? null" />
        <p class="mb-0 mt-2 px-1 text-xs"><a href="{{ route('how-it-works') }}" class="keep text-subtle underline underline-offset-4">Как задать хороший вопрос</a></p>
    @else
        {{-- Гость видит то же приглашение, что и вошедший; по нажатию его просят войти и после входа возвращают к форме (questions.create ведёт на #ask) --}}
        <a href="{{ route('questions.create') }}" class="keep ask-pulse flex items-center gap-3 rounded-card bg-white px-4 py-3 text-ink no-underline shadow-sm ring-2 ring-brand-400 sm:px-5">
            <x-ui.writing-pencil />
            <span class="flex-1 text-[15px]">Задать вопрос</span>
            <span class="text-xs text-subtle">нужен вход</span>
        </a>
    @endauth
</div>
