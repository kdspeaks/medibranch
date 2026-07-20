<x-page-layout title="Loading Medicine...">
    <x-slot name="actionButton">
        <div class="flex gap-2 items-center">
            <div class="h-10 w-48 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
            <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
            <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
        </div>
    </x-slot>

    <div class="my-5 space-y-6">
        <x-filament::card>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-4">
                    <div>
                        <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-2"></div>
                        <div class="h-8 w-64 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-1"></div>
                        <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="h-6 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-sm"></div>
                        <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-sm"></div>
                        <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-sm"></div>
                    </div>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-3xl lg:grid-cols-4">
                    @for($i = 0; $i < 4; $i++)
                        <div class="rounded-sm border border-border/80 bg-surface/60 p-3 dark:border-border-dark dark:bg-surface-dark/40">
                            <div class="h-3 w-20 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-2"></div>
                            <div class="h-8 w-12 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mt-1"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </x-filament::card>

        <div class="mt-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @for($i = 0; $i < 3; $i++)
                    <x-filament::card>
                        <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-4"></div>
                        <div class="space-y-4">
                            @for($j = 0; $j < 4; $j++)
                                <div>
                                    <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-1"></div>
                                    <div class="h-5 w-40 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                                </div>
                            @endfor
                        </div>
                    </x-filament::card>
                @endfor
            </div>
        </div>

        <!-- Skeleton for Tabs -->
        <div class="mt-8">
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8 overflow-x-auto">
                    <div class="border-b-2 border-primary-500 py-4 px-1">
                        <div class="h-5 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                    </div>
                    <div class="border-b-2 border-transparent py-4 px-1">
                        <div class="h-5 w-20 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                    </div>
                    <div class="border-b-2 border-transparent py-4 px-1">
                        <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                    </div>
                    <div class="border-b-2 border-transparent py-4 px-1">
                        <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                    </div>
                </nav>
            </div>

            <!-- Tab Content Skeleton -->
            <div class="mt-2">
                <div class="mb-4">
                    <div class="h-6 w-48 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-2"></div>
                    <div class="h-4 w-96 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                </div>
                
                <x-filament::card>
                    <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-4"></div>
                    <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-2"></div>
                    <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 animate-pulse rounded mb-2"></div>
                </x-filament::card>
            </div>
        </div>
    </div>
</x-page-layout>
