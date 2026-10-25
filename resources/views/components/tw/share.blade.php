@props(['url'])
@php
    $btn = 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-white transition-opacity hover:opacity-90';
    $encoded = urlencode($url);
@endphp
<div x-data="copyLink(@js($url))" class="flex flex-wrap items-center gap-2" aria-label="Поделиться">
    <a href="https://vk.com/share.php?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#0077ff]">ВКонтакте</a>
    <a href="https://t.me/share/url?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#0088cc]">Telegram</a>
    <a href="https://wa.me/?text={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#25D366]">WhatsApp</a>
    <button type="button" @click="copy()"
        class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3 py-2 text-sm font-bold hover:bg-brand-50">
        <span x-text="copied ? 'Скопировано' : 'Копировать ссылку'">Копировать ссылку</span>
    </button>
</div>
