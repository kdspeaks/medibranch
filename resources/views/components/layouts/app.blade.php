@props(['title' => 'Page Title', 'hasPadding' => true])
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

</head>

<body x-data="themeStore()" x-bind:class="{ 'dark': dark }" x-cloak x-init="init()"
    class="relative  text-text dark:text-text-dark antialiased bg-background dark:bg-background-dark {{ app()->getLocale() === 'bn' ? 'font-bengali' : 'font-sans' }}">
    <livewire:components.ui.nav />
    <div class="flex pt-16 overflow-hidden bg-background dark:bg-background-dark">

        <x-ui.sidebar />

        <div class="fixed inset-0 z-10 hidden bg-background-dark/50 dark:bg-background-dark/90" id="sidebarBackdrop">
        </div>
        <div class="relative flex flex-col w-full min-h-[calc(100vh-64px)] overflow-hidden lg:ml-64 "
            id="main-content">
            <main class="flex-grow">
                <div class="pt-6">
                    <div class="flex flex-col md:flex-row justify-between items-start px-4" >
                        <div class="flex flex-col justify-between h-full">
                            {{-- {{ Breadcrumbs::render() }} --}}
                            <div
                                class="">
                                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white ">
                                    {{ $title }}</h1>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 w-full mt-2 md:mt-0 md:w-auto">
                            <div class="md:ml-auto">
                                {{ $actionButton ?? null }}
                            </div>
                            {{-- <div class="mb-2">
                                {{ $search ?? null }}
                            </div> --}}
                        </div>
                    </div>
                    <div class="{{ $hasPadding ? 'px-4' : '' }}">{{ $slot }}</div>
                </div>
            </main>
            <footer
                    class="w-full p-4 text-sm text-text bg-surface border-t border-border dark:text-text-dark dark:bg-surface-dark dark:border-border-dark">
                    <div class="flex flex-col items-center justify-between gap-2 md:flex-row">
                        <div class="text-center md:text-left">
                            &copy; {{ date('Y') }} — Created by <span class="font-medium">Kunal Dutta</span> with ❤️
                        </div>
                        <div>
                            <x-ui.language-switch />
                        </div>
                    </div>
                </footer>
        </div>
    </div>
    @livewireScripts
</body>

</html>
