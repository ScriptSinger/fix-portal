@props(['heading', 'label', 'items'])
{{-- Левая навигация раздела: items = [['title' => , 'url' => , 'active' => bool], ...].
     На компьютере карточка как правые виджеты (заголовок и список с пунктирными разделителями),
     на телефоне и планшете вкладка у левого края и выезжающая панель, как фильтр прошивок. Счётчики не показываем. --}}
<nav aria-label="{{ $label }}" x-data="{ open: false }" @keydown.escape.window="open = false" class="lg:rounded-[5px] lg:bg-white lg:p-5">
    <button type="button" @click="open = true" aria-label="Открыть: {{ $heading }}"
        class="fixed left-0 top-1/2 z-30 -translate-y-1/2 rotate-180 bg-brand-400 px-[9px] py-4 text-[13px] font-bold uppercase leading-none tracking-[1.5px] text-black shadow-[2px_0_8px_rgba(0,0,0,.35)] [writing-mode:vertical-rl] lg:hidden">{{ $heading }}</button>
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-[70] bg-black/50 lg:hidden" aria-hidden="true"></div>

    <div class="fw-drawer lg:contents" :class="{ open }">
        <div class="flex min-h-14 items-center gap-3 border-b border-line bg-surface px-4 lg:hidden">
            <span class="flex-1 text-lg font-bold text-ink">{{ $heading }}</span>
            <button type="button" @click="open = false" class="px-1 text-3xl leading-none" aria-label="Закрыть">&times;</button>
        </div>
        <h2 class="mb-4 hidden text-lg font-bold leading-[18px] text-ink lg:block">{{ $heading }}</h2>
        <ul class="min-h-0 flex-1 overflow-y-auto px-4 lg:overflow-visible lg:px-0">
            @foreach ($items as $item)
                <li class="border-b border-dashed border-line last:border-0">
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                        class="-mx-4 flex items-center justify-between gap-3 px-4 py-3 text-[15px] font-bold leading-[22px] lg:-mx-5 lg:px-5 {{ $item['active'] ? 'keep bg-black text-brand-400' : 'text-ink' }}">
                        <span>{{ $item['title'] }}</span>
                        @if (! empty($item['badge']))<span class="min-w-[22px] rounded-full bg-red-600 px-1.5 text-center text-xs font-bold leading-[22px] text-white">{{ $item['badge'] }}</span>@endif
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($slot->isNotEmpty())
            <div class="border-t border-line p-4 lg:hidden">{{ $slot }}</div>
        @endif
    </div>
</nav>
