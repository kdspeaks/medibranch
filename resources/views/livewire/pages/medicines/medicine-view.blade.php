<x-page-layout title="Medicine View {{ $medicine->name }}">
    <x-slot name="actionButton">
        <x-ui.button icon="heroicon-o-pencil" variant="primary" class="w-full" wire:navigate
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
            </x-filament::card>

            <div class="mt-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-filament::card>
                        <h2 class="text-lg font-medium mb-4 text-text dark:text-text-dark">Medicine Details</h2>
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Name</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->name }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Brand</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->brand?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Category</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->category?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Form</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->form }}</div>
                            </div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <h2 class="text-lg font-medium mb-4 text-text dark:text-text-dark">Identification</h2>
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">SKU</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->sku ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Barcode</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->barcode ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Generic Name</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->generic_name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Strength</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->strength ?? '-' }}</div>
                            </div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <h2 class="text-lg font-medium mb-4 text-text dark:text-text-dark">Pricing</h2>
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Purchase Price</span>
                                <div class="font-medium text-text dark:text-text-dark">
                                    ₹{{ number_format((float) $medicine->purchase_price, 2) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Sale Price</span>
                                <div class="font-medium text-text dark:text-text-dark">
                                    ₹{{ number_format((float) $medicine->sale_price, 2) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Tax Profile</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->taxProfile?->name ?? 'Default' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Status</span>
                                <div class="font-medium">
                                    @if($medicine->is_active)
                                        <x-filament::badge color="success">Active</x-filament::badge>
                                    @else
                                        <x-filament::badge color="danger">Inactive</x-filament::badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-filament::card>
                </div>
            </div>

            <livewire:pages.medicines.components.medicine-stocks-table :medicine="$medicine" :branch-id="$scopedBranchId" />

            <livewire:pages.medicines.components.medicine-batches-table :medicine="$medicine" :branch-id="$scopedBranchId" />

            <livewire:pages.medicines.components.medicine-movements-table :medicine="$medicine" :branch-id="$scopedBranchId" />

            <livewire:pages.medicines.components.medicine-purchases-table :medicine="$medicine" :branch-id="$scopedBranchId" />
    </div>
</x-page-layout>
