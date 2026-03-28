<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title')</title>

    <meta name="keywords" content="">
    <meta name="description" content="@yield('description')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="author" content="">

    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif


    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/front/css/main.css') }}">
    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('assets/front/images/favicon_io/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/front/images/favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/front/images/favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/front/images/favicon_io/site.webmanifest') }}">
    <meta name="yandex-verification" content="7843198b5b2a0b60" />
</head>
