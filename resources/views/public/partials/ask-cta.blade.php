{{-- Главное действие раздела вопросов: чем чаще нажимают, тем больше нового контента. Стоит в левой колонке на компьютере; на телефоне та же кнопка лежит в выезжающей панели навигации (см. appliance-nav) --}}
<section class="mt-6 hidden rounded-[5px] bg-brand-400 p-5 lg:block" aria-labelledby="ask-cta-title">
    <h2 id="ask-cta-title" class="mb-2 text-lg font-bold leading-6 text-black">Не нашли ответ?</h2>
    <p class="mb-4 text-sm leading-[22px] text-black">Опишите проблему: ответят мастера и участники сообщества, а ваш вопрос поможет следующим читателям.</p>
    <x-ui.button variant="dark" :href="route('questions.create')" class="w-full">Задать вопрос</x-ui.button>
</section>
