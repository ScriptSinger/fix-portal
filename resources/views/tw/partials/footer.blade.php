@php
    $currentYear = now()->year;
    $copyright = optional($customization)->copyright ?: "2014-{$currentYear} Ufamasters.ru. Все права защищены.";
    $copyright = preg_replace('/^\s*(©|\(c\)|copyright)\s*/iu', '', $copyright);
    $copyright = preg_replace('/(\b\d{4}-)\d{4}\b/u', '${1}' . $currentYear, $copyright);
    $nav = [
        ['Статьи', route('articles.index')],
        ['Вопросы', route('questions.index')],
        ['Прошивки', route('firmwares.index')],
        ['О нас', route('about')],
    ];
    $materials = [
        ['Категории', route('categories.index')],
        ['Теги', route('tags.index')],
        ['Telegram-чат', 'https://t.me/ufamasters_chat'],
    ];
    $heading = 'mb-4 border-b-2 border-brand-400 pb-2 font-sans text-sm font-bold uppercase tracking-wide text-white';
    $link = 'text-white transition-colors hover:text-brand-400';
@endphp
<footer class="mt-8 bg-footer text-white">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
        <section>
            <a href="{{ route('articles.index') }}" class="inline-block">
                <img src="{{ asset('assets/front/images/version/ufamasters-logo-light.png') }}" alt="UfaMasters"
                    class="-ml-[14px] h-10 w-auto">
            </a>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-gray-300">
                Статьи, вопросы и прошивки для ремонта бытовой техники от опытных мастеров.
            </p>
        </section>

        <nav aria-label="Разделы сайта">
            <h2 class="{{ $heading }}">Разделы</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($nav as [$label, $url])
                    <li><a href="{{ $url }}" class="{{ $link }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav aria-label="Материалы">
            <h2 class="{{ $heading }}">Материалы</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($materials as [$label, $url])
                    <li>
                        <a href="{{ $url }}" class="{{ $link }}"
                            @if (str_starts_with($url, 'http')) target="_blank" rel="noopener" @endif>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <section>
            <h2 class="{{ $heading }}">Нужен мастер в Уфе?</h2>
            <p class="text-sm leading-relaxed text-gray-300">
                Не получилось починить самостоятельно? Мастера UfaMasters приедут и устранят неисправность.
            </p>
            <x-tw.button variant="primary" href="https://service.ufamasters.ru" target="_blank" rel="noopener"
                class="mt-4">Вызвать мастера</x-tw.button>
        </section>
    </div>
    <div class="border-t border-white/15">
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-sm text-gray-300 sm:flex-row sm:px-6">
            <span>&copy; {{ $copyright }}</span>
            <a href="{{ route('pilot.consent') }}" class="hover:text-brand-400">Согласие на обработку персональных данных</a>
        </div>
    </div>
</footer>
