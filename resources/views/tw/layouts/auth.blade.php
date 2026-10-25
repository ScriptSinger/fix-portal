<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Экраны входа сделаны в стиле AdminLTE, как в старой версии: свой шрифт и серый фон --}}
<body class="min-h-screen bg-[#e9ecef] text-base leading-6 text-[#212529] antialiased" style="font-family: 'Source Sans Pro', -apple-system, 'Segoe UI', Roboto, sans-serif">
    <div class="mx-auto flex min-h-screen w-full max-w-[360px] flex-col justify-center px-3 py-8 max-[576px]:max-w-[90%]">
        <div class="mb-5 text-center text-[33.6px] font-light leading-[50px]">
            <a href="{{ route('articles.index') }}" class="text-[#212529]"><b class="font-bold">Ufa</b>masters</a>
        </div>
        <div class="rounded bg-white p-5 text-[#666] shadow-[0_0_1px_rgba(0,0,0,.125),0_1px_3px_rgba(0,0,0,.2)]">
            @yield('content')
        </div>
    </div>
    @stack('scripts')
</body>

</html>
