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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Slab:wght@700&display=swap&subset=cyrillic"
        rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/front/images/favicon_io/favicon-32x32.png') }}">
    <meta name="yandex-verification" content="7843198b5b2a0b60" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @includeWhen(app()->environment('production'), 'components.analytics.metrika')
</head>

<body class="bg-surface font-sans text-ink antialiased">
    <a href="#content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">
        К содержимому
    </a>

    @include('tw.partials.header')

    @yield('hero')

    <main id="content" class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="min-w-0">@yield('content')</div>
            <aside>
                <div class="space-y-6 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:pr-1">
                    @yield('sidebar')
                </div>
            </aside>
        </div>
    </main>

    @include('tw.partials.footer')
    @stack('scripts')
</body>

</html>
