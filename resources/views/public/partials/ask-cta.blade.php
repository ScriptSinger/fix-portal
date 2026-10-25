{{-- Главное действие раздела вопросов: чем чаще нажимают, тем больше нового контента. Стоит в левой колонке (компьютер) и закреплена кнопкой внизу на телефоне (появляется после прокрутки, пока основная кнопка в заголовке ушла с экрана) --}}
<section class="mt-6 hidden bg-brand-400 p-5 lg:block" aria-labelledby="ask-cta-title">
    <h2 id="ask-cta-title" class="mb-2 text-lg font-bold leading-6 text-black">Не нашли ответ?</h2>
    <p class="mb-4 text-sm leading-[22px] text-black">Опишите проблему: ответят мастера и участники сообщества, а ваш вопрос поможет следующим читателям.</p>
    <x-ui.button variant="dark" :href="route('questions.create')" class="w-full">Задать вопрос</x-ui.button>
</section>
<a href="{{ route('questions.create') }}" x-data="{ show: false }" x-show="show" x-cloak @scroll.window="show = window.scrollY > 300"
    class="keep fixed bottom-4 left-4 right-20 z-30 flex h-12 items-center justify-center bg-brand-400 text-sm font-bold text-black shadow-[0_2px_10px_rgba(0,0,0,.3)] lg:hidden">Задать вопрос</a>
