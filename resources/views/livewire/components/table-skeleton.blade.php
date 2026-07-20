<div>
    <x-filament::card class="overflow-hidden p-0 animate-pulse w-full">
    <!-- Table Header Toolbar Skeleton -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="h-9 w-48 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        <div class="flex gap-2">
            <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
            <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        </div>
    </div>
    
    <!-- Table Columns Skeleton -->
    <div class="flex gap-4 p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        @for($i = 0; $i < 5; $i++)
            <div class="h-4 flex-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        @endfor
    </div>

    <!-- Table Rows Skeleton -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
        @for($i = 0; $i < 5; $i++)
            <div class="flex gap-4 p-4 items-center">
                <div class="h-4 w-1/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 w-1/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 w-1/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 w-1/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="flex gap-2 ml-auto">
                    <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
            </div>
        @endfor
    </div>
    </x-filament::card>
</div>
