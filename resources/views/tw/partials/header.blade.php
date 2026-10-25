@php
    $links = [
        ['Статьи', route('articles.index'), 'articles*'],
        ['Вопросы', route('questions.index'), 'questions*'],
        ['Прошивки', route('firmwares.index'), 'firmwares*'],
        ['О нас', route('about'), 'about'],
    ];
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('articles.index') }}" class="flex items-center">
            <img src="{{ optional($customization)->getImage('logo') ?? asset('assets/front/images/version/ufamasters-logo.png') }}"
                alt="UfaMasters" class="h-9 w-auto">
        </a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="Основная навигация">
            @foreach ($links as [$label, $url, $pattern])
                <a href="{{ $url }}" @class([
                    'rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-surface',
                    'text-ink bg-brand-50' => request()->is($pattern),
                    'text-muted' => !request()->is($pattern),
                ])>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-1 text-sm md:flex">
            @auth
                <a href="{{ route('profile.edit') }}"
                    class="rounded-md px-3 py-2 font-medium text-muted hover:bg-surface">{{ auth()->user()->name }}</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="rounded-md px-3 py-2 font-medium text-muted hover:bg-surface">Выход</button>
                </form>
            @endauth
            @guest
                <a href="{{ route('login') }}"
                    class="rounded-md px-3 py-2 font-medium text-muted hover:bg-surface">Вход</a>
                <a href="{{ route('register') }}"
                    class="rounded-md bg-ink px-3 py-2 font-medium text-white hover:bg-black">Регистрация</a>
            @endguest
        </div>

        <button type="button" class="rounded-md p-2 text-ink hover:bg-surface md:hidden" @click="open = !open"
            :aria-expanded="open" aria-controls="mobile-menu" aria-label="Меню">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path x-show="!open" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                <path x-show="open" x-cloak stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>

    <div id="mobile-menu" x-show="open" x-cloak x-transition.opacity class="border-t border-line bg-white md:hidden">
        <nav class="mx-auto flex max-w-6xl flex-col px-4 py-2 sm:px-6" aria-label="Мобильная навигация">
            @foreach ($links as [$label, $url, $pattern])
                <a href="{{ $url }}" @class([
                    'rounded-md px-3 py-3 text-base font-medium',
                    'bg-brand-50 text-ink' => request()->is($pattern),
                    'text-muted' => !request()->is($pattern),
                ])>{{ $label }}</a>
            @endforeach
            <div class="mt-2 flex gap-2 border-t border-line py-3">
                @auth
                    <a href="{{ route('profile.edit') }}"
                        class="flex-1 rounded-md border border-line px-3 py-2 text-center text-sm font-medium">{{ auth()->user()->name }}</a>
                @endauth
                @guest
                    <a href="{{ route('login') }}"
                        class="flex-1 rounded-md border border-line px-3 py-2 text-center text-sm font-medium">Вход</a>
                    <a href="{{ route('register') }}"
                        class="flex-1 rounded-md bg-ink px-3 py-2 text-center text-sm font-medium text-white">Регистрация</a>
                @endguest
            </div>
        </nav>
    </div>
</header>
