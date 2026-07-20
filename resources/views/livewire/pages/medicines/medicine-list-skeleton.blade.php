<x-page-layout title="Loading Medicines...">
    <x-slot name="actionButton">
        <div class="flex gap-2">
            <div class="h-10 w-40 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
            <div class="h-10 w-40 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
        </div>
    </x-slot>

    <div class="my-5">
        <x-filament::card class="overflow-hidden p-0">
            <!-- Table Header Toolbar Skeleton -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex gap-2">
                    <div class="h-9 w-64 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
                <div class="flex gap-2">
                    <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                    <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
            </div>
            
            <!-- Table Columns Skeleton -->
            <div class="grid grid-cols-6 gap-4 p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                @for($i = 0; $i < 6; $i++)
                    <div class="h-4 w-20 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                @endfor
            </div>

            <!-- Table Rows Skeleton -->
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @for($i = 0; $i < 5; $i++)
                    <div class="grid grid-cols-6 gap-4 p-4 items-center">
                        <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                        <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                        <div class="h-6 w-16 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-full"></div>
                        <div class="h-4 w-20 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                        <div class="h-4 w-28 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                        <div class="flex gap-2 justify-end">
                            <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                            <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Table Footer Skeleton -->
            <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="h-4 w-48 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                <div class="flex gap-2">
                    <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                    <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></div>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-page-layout>
