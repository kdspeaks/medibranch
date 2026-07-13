<x-page-layout title="{{ __('messages.dashboard') }}">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-text dark:text-text-dark">
            {{ __('messages.hi_again', ['name' => auth()->user()->name]) }}
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Today's Sales -->
        <div class="bg-surface dark:bg-surface-dark rounded-xl shadow-sm border border-border dark:border-border-dark p-6 transition-all duration-200 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.today_sales') }}</p>
                    <h3 class="text-2xl font-bold text-text dark:text-text-dark">{{ currency() }}{{ number_format($todaySalesAmount, 2) }}</h3>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        <!-- Today's Invoices -->
        <div class="bg-surface dark:bg-surface-dark rounded-xl shadow-sm border border-border dark:border-border-dark p-6 transition-all duration-200 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.today_invoices') }}</p>
                    <h3 class="text-2xl font-bold text-text dark:text-text-dark">{{ number_format($todaySalesCount) }}</h3>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <x-heroicon-o-document-text class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-surface dark:bg-surface-dark rounded-xl shadow-sm border border-border dark:border-border-dark p-6 transition-all duration-200 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.total_customers') }}</p>
                    <h3 class="text-2xl font-bold text-text dark:text-text-dark">{{ number_format($totalCustomers) }}</h3>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                    <x-heroicon-o-users class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
            </div>
        </div>

        <!-- Total Medicines -->
        <div class="bg-surface dark:bg-surface-dark rounded-xl shadow-sm border border-border dark:border-border-dark p-6 transition-all duration-200 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.total_medicines') }}</p>
                    <h3 class="text-2xl font-bold text-text dark:text-text-dark">{{ number_format($totalMedicines) }}</h3>
                </div>
                <div class="p-3 bg-teal-100 dark:bg-teal-900/30 rounded-lg">
                    <x-heroicon-o-beaker class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
