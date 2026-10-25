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
    ];
    $heading = 'mb-4 border-b-2 border-brand-400 pb-2 font-sans text-sm font-bold uppercase tracking-wide text-white';
    $link = 'text-white transition-colors hover:text-brand-400';
@endphp
<footer class="mt-8 bg-footer text-white">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
        <section>
            <a href="{{ route('articles.index') }}" class="inline-block rounded-md bg-white px-3 py-2">
                <img src="{{ optional($customization)->getImage('logo') ?? asset('assets/front/images/version/ufamasters-logo.png') }}"
                    alt="UfaMasters" class="h-8 w-auto">
            </a>
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-gray-300">
                Статьи, вопросы и прошивки для ремонта бытовой техники от опытных мастеров.
            </p>
            <x-tw.button variant="primary" size="sm" href="https://t.me/ufamasters_chat" target="_blank"
                rel="noopener" class="mt-5">Telegram-чат сообщества</x-tw.button>
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
                    <li><a href="{{ $url }}" class="{{ $link }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>
    <div class="border-t border-white/15 py-5 text-center text-sm text-gray-300">&copy; {{ $copyright }}</div>
</footer>
