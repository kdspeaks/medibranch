<x-page-layout title="Customer: {{ $customer->name }}">
    <x-slot name="actionButton">
        <x-filament::button tag="a" href="{{ route('customers') }}" color="gray" icon="heroicon-o-arrow-left">
            Back to Customers
        </x-filament::button>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Customer Details Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:col-span-1">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl font-bold">
                    {{ substr($customer->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $customer->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Customer ID: #{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Contact Information</div>
                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <x-heroicon-o-phone class="w-4 h-4 text-gray-400" />
                        {{ $customer->phone }}
                    </div>
                    @if($customer->email)
                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mt-2">
                        <x-heroicon-o-envelope class="w-4 h-4 text-gray-400" />
                        {{ $customer->email }}
                    </div>
                    @endif
                </div>

                @if($customer->address)
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Address</div>
                    <div class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                        <span>{{ $customer->address }}</span>
                    </div>
                </div>
                @endif
                
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">Member Since</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $customer->created_at->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Lifetime Stats -->
        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-center">
                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400 mb-2">
                    <div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 rounded-lg">
                        <x-heroicon-o-shopping-bag class="w-6 h-6" />
                    </div>
                    <span class="font-medium">Total Purchases</span>
                </div>
                <div class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ $totalPurchases }}
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col justify-center">
                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400 mb-2">
                    <div class="p-2 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-lg">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                    <span class="font-medium">Total Spent</span>
                </div>
                <div class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ currency() }}{{ number_format($totalSpent, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Purchase History</h3>
        </div>
        <div>
            {{ $this->table }}
        </div>
    </div>
    <x-filament-actions::modals />
</x-page-layout>
