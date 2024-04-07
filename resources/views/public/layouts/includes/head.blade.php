<head>
    <!-- Basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Site Metas -->
    <title>@yield('title')</title>



    <meta name="keywords" content="">
    <meta name="description" content="@yield('description')">
    <meta name="author" content="">

    <!-- Site Icons -->
    {{-- <link rel="shortcut icon" href="{{ asset('assets/front/images/favicon.ico') }}" type="image/x-icon" />
    <link rel="apple-touch-icon" href="{{ asset('assets/front/images/favicon.ico') }}"> --}}


    <!-- Design fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/front/css/main.css') }}">


    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('assets/front/images/favicon_io/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/front/images/favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/front/images/favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/front/images/favicon_io/site.webmanifest') }}">

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
