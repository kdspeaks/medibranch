<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Page Title' }} | Milky | Pharmacy Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @filamentStyles
    @vite('resources/css/app.css')
    @livewireStyles
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

</head>

<body x-data="themeStore()" x-bind:class="{ 'dark': dark }" x-cloak x-init="init()"
    class="relative  text-text dark:text-text-dark antialiased bg-background dark:bg-background-dark {{ app()->getLocale() === 'bn' ? 'font-bengali' : 'font-sans' }}">

    {{ $slot }}

    @livewire('notifications')
    @vite('resources/js/app.js')
    @filamentScripts
    @livewireScripts
</body>

</html>
