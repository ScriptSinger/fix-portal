@php
    $card = 'mb-6 rounded-card bg-white p-5';
@endphp
@php
@endphp
<section class="{{ $card }}">
    <h2 class="mb-4 flex items-center gap-2 text-lg font-bold leading-[18px] text-telegram">
        <x-ui.icon name="telegram" class="size-[18px]" /> Telegram-чат сообщества
    </h2>
    <p class="mb-[27px]">Делитесь опытом, задавайте вопросы, помогайте другим — знание сила!</p>
    <x-ui.button variant="telegram" href="https://t.me/ufamasters_chat" target="_blank" rel="noopener" class="w-full">
        <x-ui.icon name="telegram" class="mr-1 inline size-4 align-[-3px]" />Перейти в Telegram
    </x-ui.button>
</section>
