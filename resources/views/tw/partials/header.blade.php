@php
    $links = [
        ['Статьи', route('articles.index'), 'articles*'],
        ['Вопросы', route('questions.index'), 'questions*'],
        ['Прошивки', route('firmwares.index'), 'firmwares*'],
        ['О нас', route('about'), 'about'],
    ];
    $isActive = fn ($pattern) => request()->is($pattern, 'pilot/' . $pattern);
    $icon = 'size-5';
    $searchUrl = request()->is('pilot/*') ? route('pilot.articles') : route('articles.index');
@endphp
<header x-data="{ menu: false, search: false, account: false }" @keydown.escape.window="search = false; account = false"
    class="sticky top-0 z-40 border-b-[3px] border-brand-400 bg-footer text-white">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
        <a href="{{ route('articles.index') }}" class="flex shrink-0 items-center">
            <img src="{{ asset('assets/front/images/version/ufamasters-logo-light.png') }}" alt="UfaMasters"
                class="-ml-1.5 h-10 w-auto">
        </a>

        <nav class="ml-4 hidden items-stretch gap-1 self-stretch md:flex" aria-label="Основная навигация">
            @foreach ($links as [$label, $url, $pattern])
                <a href="{{ $url }}" @class([
                    'relative flex items-center px-3 text-[15px] transition-colors',
                    'font-bold text-white after:absolute after:inset-x-3 after:-bottom-[3px] after:h-[3px] after:bg-white' => $isActive($pattern),
                    'font-medium text-gray-300 hover:text-white' => !$isActive($pattern),
                ]) @if ($isActive($pattern)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1 sm:gap-2">
            @unless (View::hasSection('hide-header-search'))
            <button type="button" @click="search = !search; if (search) $nextTick(() => $refs.q.focus())"
                :aria-expanded="search" aria-controls="site-search" aria-label="Поиск"
                class="rounded-md p-2 text-white hover:bg-white/10">
                <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="M20 20l-3.5-3.5" />
                </svg>
            </button>
            @endunless

            <x-tw.button :href="route('questions.create')" size="sm" class="max-sm:hidden">Задать вопрос</x-tw.button>

            @auth
                <div class="relative hidden md:block" @click.outside="account = false">
                    <button type="button" @click="account = !account" :aria-expanded="account" aria-haspopup="menu"
                        class="flex items-center gap-2 rounded-full p-1 hover:bg-white/10" aria-label="Аккаунт">
                        <x-tw.avatar :user="auth()->user()" size="size-8" />
                    </button>
                    <div x-show="account" x-cloak x-transition.opacity role="menu"
                        class="absolute right-0 mt-2 w-52 rounded-xl border border-line bg-white p-1 text-sm text-ink shadow-lg">
                        <div class="truncate px-3 py-2 font-bold">{{ auth()->user()->name }}</div>
                        <a href="{{ route('profile.edit') }}" role="menuitem"
                            class="block rounded-md px-3 py-2 hover:bg-surface">Профиль</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" role="menuitem"
                                class="block w-full rounded-md px-3 py-2 text-left hover:bg-surface">Выход</button>
                        </form>
                    </div>
                </div>
            @endauth
            @guest
                <a href="{{ route('login') }}"
                    class="hidden rounded-md px-3 py-2 text-[15px] font-medium text-gray-300 hover:text-white md:block">Вход</a>
            @endguest

            <button type="button" class="rounded-md p-2 text-white hover:bg-white/10 md:hidden" @click="menu = !menu"
                :aria-expanded="menu" aria-controls="mobile-menu" aria-label="Меню">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path x-show="!menu" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    <path x-show="menu" x-cloak stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
    </div>

    @unless (View::hasSection('hide-header-search'))
    <div id="site-search" x-show="search" x-cloak x-transition.opacity class="border-t border-white/15 bg-neutral-900">
        <form method="GET" action="{{ $searchUrl }}" role="search"
            class="mx-auto flex max-w-6xl gap-2 px-4 py-3 sm:px-6">
            <label for="header-search" class="sr-only">Поиск по статьям</label>
            <input id="header-search" x-ref="q" type="search" name="text" value="{{ request('text') }}"
                placeholder="Поиск по статьям: ошибка, модель, неисправность"
                class="min-w-0 flex-1 rounded-lg border border-line bg-white px-3 py-2.5 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-400/40">
            <x-tw.button variant="primary">Найти</x-tw.button>
        </form>
    </div>
    @endunless

    <div id="mobile-menu" x-show="menu" x-cloak x-transition.opacity class="border-t border-white/15 bg-footer md:hidden">
        <nav class="mx-auto flex max-w-6xl flex-col px-4 py-2 sm:px-6" aria-label="Мобильная навигация">
            @foreach ($links as [$label, $url, $pattern])
                <a href="{{ $url }}" @class([
                    'border-l-4 px-3 py-3 text-base',
                    'border-brand-400 bg-white/10 font-bold text-white' => $isActive($pattern),
                    'border-transparent font-medium text-gray-300' => !$isActive($pattern),
                ])>{{ $label }}</a>
            @endforeach
            <div class="mt-2 flex flex-wrap gap-2 border-t border-white/15 py-3">
                <x-tw.button :href="route('questions.create')" size="sm" class="flex-1">Задать вопрос</x-tw.button>
                @auth
                    <x-tw.button variant="outline" size="sm" :href="route('profile.edit')"
                        class="flex-1">{{ auth()->user()->name }}</x-tw.button>
                @endauth
                @guest
                    <x-tw.button variant="outline" size="sm" :href="route('login')" class="flex-1">Вход</x-tw.button>
                @endguest
            </div>
        </nav>
    </div>
</header>
