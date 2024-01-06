<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    <!-- Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">

    <link rel="stylesheet" href="{{ asset('assets/admin/css/adminlte.css') }}">

    <!-- Scripts -->

</head>

<body class="@yield('body-class', '')">
    @yield('content')
    <script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>
</body>

</html>
