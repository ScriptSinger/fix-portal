@php
    // Статьи живут в /articles, /categories, /tag; вопросы в /questions и по технике в /appliances
    $links = [
        ['Статьи', route('articles.index'), ['articles*', 'categories*', 'tag/*', 'tags*']],
        ['Вопросы', route('questions.index'), ['questions*', 'appliances/*']],
        ['Прошивки', route('firmwares.index'), ['firmwares*']],
        ['О нас', route('about'), ['about']],
    ];
    $isActive = fn ($patterns) => request()->is(...$patterns);
    // Поиск в шапке ищет в том разделе, где человек сейчас: статьи, вопросы или прошивки
    [$searchUrl, $searchField, $searchLabel] = match (true) {
        request()->is('questions*', 'appliances/*') => [route('questions.index'), 'text', 'Поиск по вопросам'],
        request()->is('firmwares*') => [route('firmwares.index'), 'search', 'Поиск прошивок'],
        default => [route('articles.index'), 'text', 'Поиск по статьям'],
    };
    $link = 'keep block px-[9.6px] py-6 text-[14px] font-bold uppercase leading-[14px] text-[#121212] hover:text-black hover:opacity-70';
    $logo = optional($customization)->getImage('logo') ?? asset('assets/front/images/version/ufamasters-logo-trim.png');
@endphp
{{-- Шапка в том же контейнере, что контент и подвал: логотип, меню по центру, поиск и вход справа --}}
<header x-data="{ menu: false }" class="frame sticky top-0 z-40 bg-brand-400 text-ink">
    <div class="site-container py-[6px]">
        <div class="flex min-h-[74px] flex-wrap items-center lg:grid lg:min-h-[78px] lg:grid-cols-[1fr_auto_1fr]">
            <a href="{{ route('home') }}" class="mr-4 flex shrink-0 items-center lg:mr-0">
                <img src="{{ $logo }}" alt="UfaMasters" width="174" height="38" class="h-[38px] w-auto">
            </a>

            <nav class="hidden lg:block" aria-label="Основная навигация">
                <ul class="flex">
                    @foreach ($links as [$label, $url, $pattern])
                        <li><a href="{{ $url }}" class="{{ $link }}"
                                @if ($isActive($pattern)) aria-current="page" @endif>{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="hidden items-center justify-end gap-2 lg:flex">
                <form method="GET" action="{{ $searchUrl }}" role="search" aria-label="{{ $searchLabel }}" class="relative">
                    <label for="site-search" class="sr-only">{{ $searchLabel }}</label>
                    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-subtle" />
                    <input id="site-search" name="{{ $searchField }}" type="search" value="{{ request($searchField) }}" placeholder="{{ $searchLabel }}"
                        class="field !h-10 w-[200px] !pl-9 !pr-3 !text-sm">
                </form>
                @auth
                    @php $me = auth()->user(); $unread = $me->unreadNotifications()->count(); @endphp
                    <a href="{{ route('cabinet.notifications') }}" data-bell data-url="{{ route('cabinet.notifications.count') }}" class="keep relative grid size-11 place-items-center rounded-full text-ink" aria-label="Уведомления{{ $unread ? ': ' . $unread . ' новых' : '' }}">
                        <x-ui.icon name="bell" class="size-6" />
                        <span data-bell-badge @if (! $unread) hidden @endif class="absolute right-0 top-0 min-w-[18px] rounded-full bg-red-600 px-1 text-center text-[11px] font-bold leading-[18px] text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                    </a>
                    <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                        <button type="button" @click="open = !open" :aria-expanded="open" class="flex items-center gap-2 rounded-full p-0.5 pr-3 text-sm font-medium">
                            <x-ui.avatar :user="$me" size="size-9" /><span class="max-w-[120px] truncate">{{ $me->name }}</span>
                        </button>
                        <div x-show="open" x-cloak x-transition.opacity class="absolute right-0 top-full z-50 mt-2 w-56 rounded-xl bg-white p-1.5 text-ink shadow-xl ring-1 ring-black/10">
                            @foreach ([['Кабинет', route('cabinet.index')], ['Уведомления', route('cabinet.notifications')], ['Мои вопросы', route('cabinet.questions')], ['Профиль', route('profile.edit')]] as [$label, $url])
                                <a href="{{ $url }}" class="keep flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-black/5">{{ $label }}@if ($label === 'Уведомления' && $unread)<span class="rounded-full bg-red-600 px-1.5 text-xs font-bold text-white">{{ $unread }}</span>@endif</a>
                            @endforeach
                            <hr class="my-1 border-line">
                            <form action="{{ route('logout') }}" method="POST">@csrf
                                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-black/5">Выйти</button>
                            </form>
                        </div>
                    </div>
                @endauth
                @guest
                    <a href="{{ route('register') }}" class="{{ $link }} !py-3">Регистрация</a>
                    <x-ui.button variant="dark" :href="route('login')" class="!h-10 !py-0">Войти</x-ui.button>
                @endguest
            </div>

            <button type="button" class="ml-auto flex h-10 w-14 items-center justify-center rounded border border-white/10 bg-black lg:hidden" @click="menu = !menu" :aria-expanded="menu"
                aria-controls="mobile-menu" aria-label="Меню">
                {{-- как у navbar-toggler в Bootstrap 4: значок остаётся «гамбургером» в открытом состоянии --}}
                <svg class="size-[30px]" viewBox="0 0 30 30" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h22M4 15h22M4 23h22" />
                </svg>
            </button>
        </div>

        <nav id="mobile-menu" x-show="menu" x-collapse x-cloak class="w-full lg:hidden" aria-label="Мобильная навигация">
            <form method="GET" action="{{ $searchUrl }}" role="search" aria-label="{{ $searchLabel }} в меню" class="pt-2">
                <label for="site-search-m" class="sr-only">{{ $searchLabel }}</label>
                <input id="site-search-m" name="{{ $searchField }}" type="search" value="{{ request($searchField) }}" placeholder="{{ $searchLabel }}" class="field !h-11 !text-sm">
            </form>
            <ul class="pt-2">
                @foreach ($links as [$label, $url, $pattern])
                    <li><a href="{{ $url }}" class="{{ $link }} !px-0 !py-4 !text-[13px] !leading-[13px]">{{ $label }}</a></li>
                @endforeach
                @auth
                    <li><a href="{{ route('cabinet.index') }}" class="{{ $link }} !px-0 !py-4 !text-[13px] !leading-[13px]">Кабинет</a></li>
                    <li><a href="{{ route('cabinet.notifications') }}" class="{{ $link }} !px-0 !py-4 !text-[13px] !leading-[13px]">Уведомления @if (auth()->user()->unreadNotifications()->count())({{ auth()->user()->unreadNotifications()->count() }})@endif</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">@csrf
                            <button type="submit" class="{{ $link }} !px-0 !py-4 !text-[13px] !leading-[13px]">Выход</button>
                        </form>
                    </li>
                @endauth
                @guest
                    <li><a href="{{ route('register') }}" class="{{ $link }} !px-0 !py-4 !text-[13px] !leading-[13px]">Регистрация</a></li>
                    <li><a href="{{ route('login') }}" class="{{ $link }} !px-0 !py-4 !text-[13px] !leading-[13px]">Вход</a></li>
                @endguest
            </ul>
        </nav>
    </div>
</header>
