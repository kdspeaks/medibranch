<x-page-layout title="Medicine View {{ $medicine->name }}">
    <x-slot name="actionButton">
        <x-ui.button icon="fas-pen" variant="primary" class="w-full" wire:navigate
            href="{{ route('medicines.edit', ['medicine' => $medicine]) }}">
            Edit Medicine
        </x-ui.button>
    </x-slot>

    @php
        $stockTone = match ($summary['stock_state']) {
            'In stock' => 'bg-success/10 text-success dark:text-success-dark',
            'Low stock' => 'bg-warning/10 text-warning dark:text-warning-dark',
            default => 'bg-error/10 text-error dark:text-error-dark',
        };

        $typeTone = function (string $type): string {
            return match ($type) {
                'in' => 'bg-success/10 text-success dark:text-success-dark',
                'out' => 'bg-error/10 text-error dark:text-error-dark',
                'adjustment' => 'bg-info/10 text-info dark:text-info-dark',
                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
            };
        };

        $alertTone = function (string $tone): string {
            return match ($tone) {
                'success' => 'bg-success/10 text-success dark:text-success-dark',
                'warning' => 'bg-warning/10 text-warning dark:text-warning-dark',
                'danger' => 'bg-error/10 text-error dark:text-error-dark',
                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
            };
        };
    @endphp

    <div class="my-5 space-y-6">
        <x-filament::card>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Medicine</p>
                        <h1 class="text-2xl font-semibold text-text dark:text-text-dark">{{ $medicine->name }}</h1>
                        <p class="mt-1 text-sm text-text-muted dark:text-text-muted-dark">
                            {{ $branchLabel }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-sm px-2.5 py-1 text-xs font-medium {{ $stockTone }}">
                            {{ $summary['stock_state'] }}
                        </span>
                        @foreach ($alerts as $alert)
                            <span class="inline-flex items-center rounded-sm px-2.5 py-1 text-xs font-medium {{ $alertTone($alert['tone']) }}">
                                {{ $alert['label'] }}: {{ $alert['value'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-3xl lg:grid-cols-4">
                    <div class="rounded-sm border border-border/80 bg-surface/60 p-3 dark:border-border-dark dark:bg-surface-dark/40">
                        <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Current stock</p>
                        <p class="mt-1 text-2xl font-semibold text-text dark:text-text-dark">{{ $summary['total_stock'] }}</p>
                    </div>
                    <div class="rounded-sm border border-border/80 bg-surface/60 p-3 dark:border-border-dark dark:bg-surface-dark/40">
                        <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Batches</p>
                        <p class="mt-1 text-2xl font-semibold text-text dark:text-text-dark">{{ $summary['batch_count'] }}</p>
                    </div>
                    <div class="rounded-sm border border-border/80 bg-surface/60 p-3 dark:border-border-dark dark:bg-surface-dark/40">
                        <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Purchases</p>
                        <p class="mt-1 text-2xl font-semibold text-text dark:text-text-dark">{{ $summary['purchase_count'] }}</p>
                    </div>
                    <div class="rounded-sm border border-border/80 bg-surface/60 p-3 dark:border-border-dark dark:bg-surface-dark/40">
                        <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Movements</p>
                        <p class="mt-1 text-2xl font-semibold text-text dark:text-text-dark">{{ $summary['movement_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Barcode</p>
                    <p class="mt-1 font-mono text-sm text-text dark:text-text-dark">{{ $medicine->barcode }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">SKU</p>
                    <p class="mt-1 font-mono text-sm text-text dark:text-text-dark">{{ $medicine->sku }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Form</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">{{ $medicine->form ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Potency</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">{{ $medicine->potency ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Packing</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">{{ $medicine->packing_label }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Manufacturer</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">{{ $medicine->manufacturer?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Tax</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">
                        {{ $medicine->tax?->name ?? '-' }}
                        @if($medicine->tax?->rate !== null)
                            <span class="text-text-muted dark:text-text-muted-dark">({{ $medicine->tax->rate }}%)</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Status</p>
                    <p class="mt-1 text-sm">
                        <span class="inline-flex rounded-sm px-2 py-1 font-medium {{ $medicine->is_active ? 'bg-success/10 text-success dark:text-success-dark' : 'bg-error/10 text-error dark:text-error-dark' }}">
                            {{ $medicine->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Purchase price</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">&#8377;{{ number_format((float) $medicine->purchase_price, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Sale price</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">&#8377;{{ number_format((float) $medicine->sale_price, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Margin</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">{{ number_format((float) $medicine->margin, 2) }}%</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Discount</p>
                    <p class="mt-1 text-sm text-text dark:text-text-dark">{{ number_format((float) $medicine->discount_on_sale, 2) }}%</p>
                </div>
            </div>

            @if ($medicine->description)
                <div class="mt-6 border-t border-border pt-4 dark:border-border-dark">
                    <p class="text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">Description</p>
                    <div class="prose prose-sm mt-2 max-w-none dark:prose-invert">
                        {!! $medicine->description !!}
                    </div>
                </div>
            @endif
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-text dark:text-text-dark">Current Stock</h2>
                    <p class="text-sm text-text-muted dark:text-text-muted-dark">Branch-wise available stock for this medicine.</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-border dark:divide-border-dark">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">
                            <th class="px-3 py-2">Branch</th>
                            <th class="px-3 py-2">Code</th>
                            <th class="px-3 py-2">Available</th>
                            <th class="px-3 py-2">Batches</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        @forelse ($branchStocks as $row)
                            <tr class="text-sm text-text dark:text-text-dark">
                                <td class="px-3 py-3 font-medium">{{ $row['branch_name'] }}</td>
                                <td class="px-3 py-3 font-mono text-xs">{{ $row['branch_code'] }}</td>
                                <td class="px-3 py-3">{{ $row['quantity'] }}</td>
                                <td class="px-3 py-3">{{ $row['batch_count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-sm text-text-muted dark:text-text-muted-dark">
                                    No stock records found for the selected scope.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-border dark:divide-border-dark">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">
                            <th class="px-3 py-2">Branch</th>
                            <th class="px-3 py-2">Batch</th>
                            <th class="px-3 py-2">Available</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Purchase</th>
                            <th class="px-3 py-2">Margin</th>
                            <th class="px-3 py-2">MFG</th>
                            <th class="px-3 py-2">Expiry</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        @forelse ($batches as $batch)
                            <tr class="text-sm text-text dark:text-text-dark">
                                <td class="px-3 py-3">{{ $batch['branch_name'] }}</td>
                                <td class="px-3 py-3 font-mono text-xs">{{ $batch['batch_number'] }}</td>
                                <td class="px-3 py-3 font-semibold">{{ $batch['available_quantity'] }}</td>
                                <td class="px-3 py-3">{{ $batch['quantity'] }}</td>
                                <td class="px-3 py-3">&#8377;{{ number_format($batch['unit_purchase_price'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($batch['margin'], 2) }}%</td>
                                <td class="px-3 py-3">{{ $batch['mfg_date'] }}</td>
                                <td class="px-3 py-3">
                                    <span class="{{ $batch['is_expiring_soon'] ? 'text-warning dark:text-warning-dark font-semibold' : '' }}">
                                        {{ $batch['expiry_date'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-sm px-2 py-1 text-xs font-medium {{ $batch['status'] === 'active' ? 'bg-success/10 text-success dark:text-success-dark' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                                        {{ $batch['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-sm text-text-muted dark:text-text-muted-dark">
                                    No active batches available right now.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div>
                <h2 class="text-lg font-semibold text-text dark:text-text-dark">Stock Transactions</h2>
                <p class="text-sm text-text-muted dark:text-text-muted-dark">Inventory movement history for this medicine.</p>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-border dark:divide-border-dark">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Branch</th>
                            <th class="px-3 py-2">Type</th>
                            <th class="px-3 py-2">Batch</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2">Reason</th>
                            <th class="px-3 py-2">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        @forelse ($movements as $movement)
                            <tr class="text-sm text-text dark:text-text-dark">
                                <td class="px-3 py-3 whitespace-nowrap">{{ $movement['created_at'] }}</td>
                                <td class="px-3 py-3">{{ $movement['branch_name'] }}</td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-sm px-2 py-1 text-xs font-medium {{ $typeTone($movement['type']) }}">
                                        {{ $movement['type'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 font-mono text-xs">{{ $movement['batch_number'] }}</td>
                                <td class="px-3 py-3">{{ $movement['quantity'] }}</td>
                                <td class="px-3 py-3">{{ $movement['reason'] }}</td>
                                <td class="px-3 py-3">{{ $movement['source_label'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-sm text-text-muted dark:text-text-muted-dark">
                                    No stock movements found yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div>
                <h2 class="text-lg font-semibold text-text dark:text-text-dark">Purchase History</h2>
                <p class="text-sm text-text-muted dark:text-text-muted-dark">Purchases that introduced this medicine into stock.</p>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-border dark:divide-border-dark">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-text-muted dark:text-text-muted-dark">
                            <th class="px-3 py-2">Purchase</th>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Branch</th>
                            <th class="px-3 py-2">Supplier</th>
                            <th class="px-3 py-2">Batch</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2">Unit Price</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        @forelse ($purchases as $purchase)
                            <tr class="text-sm text-text dark:text-text-dark">
                                <td class="px-3 py-3 font-mono text-xs">{{ $purchase['purchase_ref'] ?: '-' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $purchase['purchase_date'] }}</td>
                                <td class="px-3 py-3">{{ $purchase['branch_name'] }}</td>
                                <td class="px-3 py-3">{{ $purchase['supplier_name'] }}</td>
                                <td class="px-3 py-3 font-mono text-xs">{{ $purchase['batch_number'] }}</td>
                                <td class="px-3 py-3">{{ $purchase['quantity'] }}</td>
                                <td class="px-3 py-3">&#8377;{{ number_format($purchase['unit_price'], 2) }}</td>
                                <td class="px-3 py-3">&#8377;{{ number_format($purchase['line_total_amount'], 2) }}</td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-sm px-2 py-1 text-xs font-medium {{ $purchase['status'] === 'stocked' ? 'bg-success/10 text-success dark:text-success-dark' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                                        {{ $purchase['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-sm text-text-muted dark:text-text-muted-dark">
                                    No purchase history found for this medicine.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::card>
    </div>
</x-page-layout>
