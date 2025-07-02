@props([
    'title' => 'Page Title',
    'hasPadding' => true,
    'actionButton' => null,
])
<x-slot name="title">
    {{ $title }}
</x-slot>
<div>
    <livewire:components.ui.nav />
    <div class="flex pt-16 overflow-hidden bg-background dark:bg-background-dark">
    
        <x-ui.sidebar />
    
        <div class="fixed inset-0 z-10 hidden bg-background-dark/50 dark:bg-background-dark/90" id="sidebarBackdrop">
        </div>
        <div class="relative flex flex-col w-full min-h-[calc(100vh-64px)] overflow-hidden lg:ml-64 " id="main-content">
            <main class="flex-grow">
                <div class="pt-6">
                    <div class="flex flex-col md:flex-row justify-between items-start px-4">
                        <div class="flex flex-col justify-between h-full">
                            {{-- {{ Breadcrumbs::render() }} --}}
                            <div class="">
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
</div>
