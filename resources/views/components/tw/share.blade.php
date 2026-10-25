@props(['url'])
@php
    $btn = 'inline-flex items-center rounded-full border border-line bg-white px-3 py-1.5 text-sm font-medium text-muted transition-colors';
    $encoded = urlencode($url);
@endphp
<div x-data="copyLink(@js($url))" class="flex flex-wrap items-center gap-2" role="group" aria-label="Поделиться">
    <span class="mr-1 text-sm text-muted">Поделиться:</span>
    <a href="https://vk.com/share.php?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} hover:border-[#0077ff] hover:text-[#0077ff]">ВКонтакте</a>
    <a href="https://t.me/share/url?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} hover:border-[#0088cc] hover:text-[#0088cc]">Telegram</a>
    <a href="https://wa.me/?text={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} hover:border-[#25D366] hover:text-[#1da851]">WhatsApp</a>
    <button type="button" @click="copy()" class="{{ $btn }} hover:border-ink hover:text-ink">
        <span x-text="copied ? 'Скопировано' : 'Копировать ссылку'">Копировать ссылку</span>
    </button>
</div>
