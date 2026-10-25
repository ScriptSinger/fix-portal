<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap&subset=cyrillic"
        rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/front/images/favicon_io/favicon-32x32.png') }}">
    <meta name="yandex-verification" content="7843198b5b2a0b60" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @includeWhen(app()->environment('production'), 'components.analytics.metrika')
</head>

<body class="bg-white font-sans text-[15px] leading-[26px] text-body antialiased">
    <a href="#content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">
        К содержимому
    </a>

    @include('tw.partials.header')

    @yield('hero')

    <main id="content" class="bg-surface pb-16 @yield('main-pt', 'pt-16')">
        <div @class(['mx-auto px-[15px]', 'sm:max-w-[540px] md:max-w-[720px] lg:max-w-[960px] xl:max-w-[1140px]' => ! View::hasSection('wide')])>
            @hasSection('no-sidebar')
                <div class="min-w-0">@yield('content')</div>
            @elseif (View::hasSection('wide'))
                <div class="grid gap-[30px] lg:grid-cols-[210px_minmax(0,1fr)]">
                    <aside>@yield('sidebar')</aside>
                    <div class="min-w-0">@yield('content')</div>
                </div>
            @else
                {{-- Колонки как в Bootstrap: 8/12 + 4/12 с отступами по 15 px --}}
                <div class="-mx-[15px] grid lg:grid-cols-[2fr_1fr]">
                    <div class="min-w-0 px-[15px]">@yield('content')</div>
                    <aside class="px-[15px]">@yield('sidebar')</aside>
                </div>
            @endif
        </div>
    </main>

    @include('tw.partials.footer')
    <x-tw.flash />

    <button type="button" x-data="{ show: false }" x-show="show" x-cloak
        @scroll.window="show = window.scrollY > 300"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })" aria-label="Наверх"
        class="fixed bottom-10 right-6 z-30 flex size-10 items-center justify-center bg-brand-400 text-ink">
        <x-tw.icon name="chevron-up" class="size-5" />
    </button>
    @stack('scripts')
</body>

</html>
