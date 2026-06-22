<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'تخته اسکرام' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#F0F4F8] min-h-screen">
    @yield('body')
    @stack('scripts')
</body>
</html>
