<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
</head>

<body x-data="themeStore()" x-bind:class="{ 'dark': dark }" x-cloak x-init="init()"
    class="relative overflow-hidden text-text dark:text-text-dark antialiased bg-background dark:bg-background-dark {{ app()->getLocale() === 'bn' ? 'font-bengali' : 'font-sans' }}">
    <!-- Dark mode toggle -->
    <x-ui.theme-toggle class="fixed top-3 right-3 z-50" />
    <div class="min-h-screen flex flex-col sm:flex-row">

        <!-- Left side image panel with fallback + overlay -->
        <div class="relative hidden sm:flex w-full sm:w-11/12">
            <div class="w-full h-full bg-cover bg-center transition duration-300 ease-in-out"
                style="background-image: url('https://picsum.photos/1280.webp?random=1');">
            </div>
            <div class="absolute inset-0 dark:bg-black/60"></div>
        </div>


        <!-- Right side login form area -->
        <div
            class="flex w-full sm:w-1/2 items-center justify-center p-6 sm:p-12 bg-surface dark:bg-surface-dark shadow-[ -12px_0_24px_-12px_rgba(0,0,0,0.1)] dark:shadow-[ -12px_0_24px_-12px_rgba(255,255,255,0.05)]">
            <div class="w-full max-w-md">
                {{ $slot }}

            </div>

        </div>

    </div>
    <x-ui.language-switch />

    {{-- <script src="https://unpkg.com/alpinejs" defer></script> --}}
</body>

</html>
