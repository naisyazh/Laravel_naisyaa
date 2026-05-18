<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toko Buku Online')</title>

    @include('layouts.style-global')
    @yield('style')
</head>

<body class="public-order-page">
    @yield('content')

    @include('layouts.js-global')
    @yield('script')
</body>

</html>
