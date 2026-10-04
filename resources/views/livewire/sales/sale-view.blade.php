<x-page-layout title="Sale #{{ $sale->invoice_number }}">
    <x-slot name="actionButton">
        <x-filament::button tag="a" href="{{ url()->previous() }}" color="gray" icon="heroicon-o-arrow-left">
            Back
        </x-filament::button>
    </x-slot>

    <div class="w-full space-y-6 mt-4">
        <!-- Sale Header Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-base font-medium text-slate-900 dark:text-white">Date: {{ $sale->sale_date->format('d M Y, h:i A') }}</div>
                    <div class="mt-4 space-y-2">
                        <div class="text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-300">Customer:</span>
                            <span class="text-slate-600 dark:text-slate-400">{{ $sale->customer ? $sale->customer->name : __('messages.walk_in') ?? 'Walk-in Customer' }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-300">Branch:</span>
                            <span class="text-slate-600 dark:text-slate-400">{{ $sale->branch->name }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-300">Cashier:</span>
                            <span class="text-slate-600 dark:text-slate-400">{{ $sale->user->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="md:text-right space-y-2">
                    <div class="inline-flex flex-col gap-2">
                        <div class="px-3 py-1 rounded-full text-xs font-medium {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($sale->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                            Payment: {{ ucfirst($sale->payment_status) }}
                        </div>
                        <div class="px-3 py-1 rounded-full text-xs font-medium {{ $sale->status === 'completed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' }}">
                            Status: {{ ucfirst($sale->status) }}
                        </div>
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-2">
                            Method: {{ strtoupper($sale->payment_method) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sale Items -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-slate-700 dark:text-slate-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 font-medium">Item</th>
                            <th class="px-6 py-3 font-medium text-right">Price</th>
                            <th class="px-6 py-3 font-medium text-right">Qty</th>
                            <th class="px-6 py-3 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($sale->items as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $item->medicine->name }}</div>
                                @if($item->medicine->manufacturer)
                                <div class="text-xs text-slate-500 mt-0.5">Brand: {{ $item->medicine->manufacturer->name }}</div>
                                @endif
                                @if($item->inventoryBatch)
                                <div class="text-xs text-slate-500 mt-0.5">Batch: {{ $item->inventoryBatch->batch_number }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">{{ currency() }}{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-900 dark:text-white">{{ currency() }}{{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sale Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex justify-end">
            <div class="w-full max-w-sm space-y-3 text-sm">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Subtotal</span>
                    <span>{{ currency() }}{{ number_format($sale->sub_total, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Discount</span>
                    <span>{{ currency() }}{{ number_format($sale->discount, 2) }}</span>
                </div>
                @if($sale->branch->taxable)
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Tax</span>
                    <span>{{ currency() }}{{ number_format($sale->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Round Off</span>
                    <span>{{ currency() }}{{ number_format($sale->round_off, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between font-bold text-lg text-slate-900 dark:text-white">
                    <span>Total Amount</span>
                    <span>{{ currency() }}{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
