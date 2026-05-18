<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi Koleksi Buku')</title>

    @include('layouts.style-global')
    
    @include('layouts.style-page')
</head>
