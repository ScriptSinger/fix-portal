@props(['url', 'align' => 'center', 'compact' => false])
{{-- compact: квадратные кнопки-иконки в одну строку (страницы статьи и прошивки); обычный вид: кнопки с подписью --}}
@php
    $btn = $compact
        ? 'btn inline-flex size-9 items-center justify-center rounded-[3px] text-white transition-opacity hover:opacity-90'
        : 'btn inline-flex h-10 items-center justify-center gap-2.5 rounded-[3px] px-4 text-sm leading-[17.5px] font-bold text-white transition-opacity hover:opacity-90';
    $caption = $compact ? 'hidden' : 'max-md:hidden';
    $wide = $compact ? '' : 'max-md:min-w-[55px]';
    $encoded = urlencode($url);
@endphp
<div x-data="copyLink(@js($url))" class="flex flex-wrap gap-1 {{ $align === 'center' ? 'justify-center' : 'justify-start' }}" role="group" aria-label="Поделиться">
    <a href="https://vk.com/share.php?url={{ $encoded }}" target="_blank" rel="noopener" aria-label="Поделиться во ВКонтакте"
        class="{{ $btn }} bg-[#3b5998] {{ $wide }}"><x-ui.icon name="vk" class="size-3.5" /><span class="{{ $caption }}">Поделиться</span></a>
    <a href="https://t.me/share/url?url={{ $encoded }}" target="_blank" rel="noopener" aria-label="Поделиться в Telegram"
        class="{{ $btn }} bg-telegram {{ $wide }}"><x-ui.icon name="telegram" class="size-3.5" /><span class="{{ $caption }}">Поделиться</span></a>
    <a href="https://wa.me/?text={{ $encoded }}" target="_blank" rel="noopener" aria-label="WhatsApp"
        class="{{ $btn }} bg-[#25d366]"><x-ui.icon name="whatsapp" class="size-3.5" /></a>
    <button type="button" @click="copy()" :title="copied ? 'Скопировано' : 'Копировать ссылку'" aria-label="Копировать ссылку"
        class="{{ $btn }} bg-[#da5333]"><x-ui.icon name="link" class="size-3.5" /></button>
</div>
