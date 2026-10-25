@php
    $links = [
        ['Статьи', route('articles.index'), 'articles*'],
        ['Вопросы', route('questions.index'), 'questions*'],
        ['Прошивки', route('firmwares.index'), 'firmwares*'],
        ['О нас', route('about'), 'about'],
    ];
    $isActive = fn ($pattern) => request()->is($pattern, 'pilot/' . $pattern);
    $link = 'block px-[9.6px] py-6 text-[14px] font-bold uppercase leading-[14px] text-[#121212] hover:text-white';
@endphp
<header x-data="{ menu: false }"
    class="sticky top-0 z-40 border-[6px] border-brand-400 bg-brand-400 text-ink shadow-[inset_0_0_0_1px_#000]">
    <div class="flex min-h-[74px] flex-wrap items-center px-4 py-2 lg:min-h-[78px] lg:px-12">
        <a href="{{ route('articles.index') }}" class="mr-4 flex shrink-0 items-center">
            <img src="{{ optional($customization)->getImage('logo') ?? asset('assets/front/images/version/ufamasters-logo.png') }}"
                alt="UfaMasters" class="h-[50px] w-[200px]">
        </a>

        <nav class="hidden flex-1 items-center justify-between lg:flex" aria-label="Основная навигация">
            <ul class="flex">
                @foreach ($links as [$label, $url, $pattern])
                    <li><a href="{{ $url }}" class="{{ $link }}"
                            @if ($isActive($pattern)) aria-current="page" @endif>{{ $label }}</a></li>
                @endforeach
            </ul>
            <ul class="flex">
                @auth
                    <li><a href="{{ route('profile.edit') }}" class="{{ $link }}">{{ auth()->user()->name }}</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="{{ $link }}">Выход</button>
                        </form>
                    </li>
                @endauth
                @guest
                    <li><a href="{{ route('register') }}" class="{{ $link }}">Регистрация</a></li>
                    <li><a href="{{ route('login') }}" class="{{ $link }}">Вход</a></li>
                @endguest
            </ul>
        </nav>

        <button type="button" class="ml-auto flex h-10 w-14 items-center justify-center rounded bg-black text-white lg:hidden border border-white/10" @click="menu = !menu" :aria-expanded="menu"
            aria-controls="mobile-menu" aria-label="Меню">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path x-show="!menu" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                <path x-show="menu" x-cloak stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>

        <nav id="mobile-menu" x-show="menu" x-cloak class="w-full lg:hidden" aria-label="Мобильная навигация">
            <ul class="border-t border-black/20 py-2">
                @foreach ($links as [$label, $url, $pattern])
                    <li><a href="{{ $url }}" class="{{ $link }} !py-3">{{ $label }}</a></li>
                @endforeach
                @auth
                    <li><a href="{{ route('profile.edit') }}" class="{{ $link }} !py-3">{{ auth()->user()->name }}</a></li>
                @endauth
                @guest
                    <li><a href="{{ route('register') }}" class="{{ $link }} !py-3">Регистрация</a></li>
                    <li><a href="{{ route('login') }}" class="{{ $link }} !py-3">Вход</a></li>
                @endguest
            </ul>
        </nav>
    </div>
</header>
