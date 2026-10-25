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
    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('assets/front/images/favicon_io/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/front/images/favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/front/images/favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/front/images/favicon_io/site.webmanifest') }}">
    <meta property="og:site_name" content="{{ config('app.name', 'Ufamasters') }}">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:type" content="@yield('og-type', 'website')">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:url" content="@hasSection('canonical')@yield('canonical')@else{{ url()->current() }}@endif">
    @php $ogImage = trim($__env->yieldContent('og-image')) ?: asset('assets/front/images/power_unit.jpg'); @endphp
    <meta property="og:image" content="{{ \Illuminate\Support\Str::startsWith($ogImage, ['http://', 'https://']) ? $ogImage : url($ogImage) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="yandex-verification" content="7843198b5b2a0b60" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @includeWhen(app()->environment('production'), 'components.analytics.metrika')
</head>

<body class="flex min-h-screen flex-col bg-white font-sans text-[15px] leading-[26px] text-body antialiased">
    <a href="#content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">
        К содержимому
    </a>

    @include('public.partials.header')

    @yield('hero')

    <main id="content" class="flex-1 bg-surface pb-16 @yield('main-pt', 'pt-16') @if (View::hasSection('bare')) flex flex-col justify-center @endif">
        @hasSection('bare')
            @yield('content')
        @else
        <div class="site-container">
            @yield('above')
            @hasSection('no-sidebar')
                <div class="min-w-0">@yield('content')</div>
            @elseif (View::hasSection('wide'))
                <div class="grid gap-[30px] lg:grid-cols-[210px_minmax(0,1fr)]">
                    <aside>@yield('sidebar')</aside>
                    <div class="min-w-0">@yield('content')</div>
                </div>
            @elseif (View::hasSection('sidebar-left'))
                {{-- Три колонки: слева навигация по разделу, в центре контент, справа реклама и действия --}}
                <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)] xl:grid-cols-[260px_minmax(0,1fr)_300px]">
                    <aside aria-label="Навигация по разделу" @class(['min-w-0 lg:sticky lg:top-[112px] lg:self-start', 'max-lg:contents' => View::hasSection('left-drawer')])>@yield('sidebar-left')</aside>
                    <div class="min-w-0">@yield('content')</div>
                    <aside aria-label="Сервисы и популярное" class="min-w-0 lg:col-span-2 xl:col-span-1">@yield('sidebar')</aside>
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                    <div class="min-w-0">@yield('content')</div>
                    <aside class="min-w-0">@yield('sidebar')</aside>
                </div>
            @endif
        </div>
        @endif
    </main>

    @hasSection('slim-footer')
        @include('public.partials.footer-slim')
    @else
        @include('public.partials.footer')
    @endif
    <x-ui.flash />

    <div id="lightbox" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-label="Изображение">
        <div class="max-h-full w-full max-w-[800px] overflow-y-auto rounded border border-black/20 bg-white">
            <div class="flex justify-end border-b border-[#e9ecef] p-4">
                <button type="button" data-lightbox-close class="text-2xl leading-none text-black/50 hover:text-black" aria-label="Закрыть">&times;</button>
            </div>
            <div class="p-4"><img src="" alt="" class="w-full"></div>
        </div>
    </div>

    <button type="button" x-data="{ show: false }" x-show="show" x-cloak
        @scroll.window="show = window.scrollY > 300"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })" aria-label="Наверх"
        class="fixed bottom-10 right-6 z-30 flex size-10 items-center justify-center bg-brand-400 text-ink">
        <x-ui.icon name="chevron-up" class="size-5" />
    </button>
    @stack('scripts')
</body>

</html>
