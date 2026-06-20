<x-page-layout title="Purchase {{ trim(($purchase->ref_code_prefix ?? '') . $purchase->ref_code_count) }}">
    <x-slot name="actionButton">
        <div class="flex gap-2">
            @if ($purchase->status !== 'received')
                <x-ui.button icon="heroicon-o-pencil" variant="primary" wire:navigate
                    href="{{ route('medicines.purchases.edit', ['purchase' => $purchase]) }}">
                    Edit Purchase
                </x-ui.button>
            @endif
            <x-ui.button icon="heroicon-o-list-bullet" variant="outline" wire:navigate href="{{ route('medicines.purchases.list') }}">
                Back to Purchases
            </x-ui.button>
        </div>
    </x-slot>

    <div class="my-5 space-y-6">
        <x-filament::card>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Reference No</p>
                    <p class="font-medium text-text dark:text-text-dark">
                        {{ trim(($purchase->ref_code_prefix ?? '') . $purchase->ref_code_count) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Invoice No</p>
                    <p class="font-medium text-text dark:text-text-dark">{{ $purchase->invoice_number ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Branch</p>
                    <p class="font-medium text-text dark:text-text-dark">{{ $purchase->branch?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Supplier</p>
                    <p class="font-medium text-text dark:text-text-dark">{{ $purchase->supplier?->name ?? 'Walk-in' }}</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Purchase Date</p>
                    <p class="font-medium text-text dark:text-text-dark">{{ $purchase->purchase_date?->format('d M, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Status</p>
                    <p class="font-medium capitalize text-text dark:text-text-dark">{{ $purchase->status }}</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Total</p>
                    <p class="font-medium text-text dark:text-text-dark">₹{{ number_format((float) $purchase->total_amount, 2) }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border dark:divide-border-dark">
                    <thead>
                        <tr class="text-left text-sm text-text-muted dark:text-text-muted-dark">
                            <th class="px-3 py-2">Medicine</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2">Batch</th>
                            <th class="px-3 py-2">Mfg</th>
                            <th class="px-3 py-2">Expiry</th>
                            <th class="px-3 py-2">Unit Price</th>
                            <th class="px-3 py-2">Tax</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        @foreach ($purchase->items as $item)
                            <tr class="text-sm text-text dark:text-text-dark">
                                <td class="px-3 py-2">{{ $item->medicine?->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $item->quantity }}</td>
                                <td class="px-3 py-2">{{ $item->batch_number ?: '-' }}</td>
                                <td class="px-3 py-2">{{ $item->mfg_date?->format('d M, Y') ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $item->expiry_date?->format('d M, Y') ?? '-' }}</td>
                                <td class="px-3 py-2">₹{{ number_format((float) $item->unit_purchase_price, 2) }}</td>
                                <td class="px-3 py-2">₹{{ number_format((float) $item->tax_amount, 2) }}</td>
                                <td class="px-3 py-2">₹{{ number_format((float) $item->line_total_amount, 2) }}</td>
                                <td class="px-3 py-2 capitalize">{{ $item->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>
    </div>
</x-page-layout>
