<x-page-layout title="Medicine View {{ $medicine->name }}">
    <x-slot name="actionButton">
        <div class="flex gap-2 items-center">
            @if($this->availableBranches->count() > 1 || auth()->user()?->hasRole('Super Admin'))
                <select wire:model.live="scopedBranchId" 
                    class="block w-48 rounded-lg border bg-input-bg text-input-text placeholder-input-placeholder border-input-border dark:bg-input-bg-dark dark:text-input-text-dark dark:placeholder-input-placeholder dark:border-input-border-dark sm:text-sm focus:ring-primary focus:border-primary">
                    @if(auth()->user()?->hasRole('Super Admin'))
                        <option value="">All Branches</option>
                    @endif
                    @foreach($this->availableBranches as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            <div class="shrink-0">
                {{ $this->adjustStockAction }}
            </div>
            <x-ui.button icon="heroicon-o-pencil" variant="primary" class="shrink-0" wire:navigate
                href="{{ route('medicines.edit', ['medicine' => $medicine]) }}">
                Edit Medicine
            </x-ui.button>
        </div>
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
                            <!-- <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Name</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->name }}</div>
                            </div> -->
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Manufacturer</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->manufacturer?->name ?? '-' }}</div>
                            </div>
                            <!-- <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Category</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->category?->name ?? '-' }}</div>
                            </div> -->
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Potency</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->potency ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Form</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->medicineForm?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Packing</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->packing_quantity }} {{ $medicine->medicineUnit?->name ?? '-' }}</div>
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
                                <div class="font-medium text-text dark:text-text-dark flex flex-col gap-2 mt-1">
                                    @if($medicine->barcode)
                                    <div class="bg-white p-2 rounded-lg inline-block self-start border border-gray-200 dark:border-gray-700" 
                                         x-data="{ 
                                            initBarcode() {
                                                if (typeof JsBarcode === 'undefined') {
                                                    const script = document.createElement('script');
                                                    script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js';
                                                    script.onload = () => this.renderBarcode();
                                                    document.head.appendChild(script);
                                                } else {
                                                    this.renderBarcode();
                                                }
                                            },
                                            renderBarcode() {
                                                JsBarcode(this.$refs.barcode, '{{ $medicine->barcode }}', {
                                                    format: 'CODE128',
                                                    width: 1.5,
                                                    height: 40,
                                                    displayValue: true,
                                                    margin: 0,
                                                    background: '#ffffff',
                                                    lineColor: '#000000',
                                                    fontSize: 14
                                                });
                                            }
                                         }" 
                                         x-init="initBarcode()">
                                        <svg x-ref="barcode" class="max-w-full"></svg>
                                    </div>
                                    @else
                                        <span>-</span>
                                    @endif
                                </div>
                            </div>
                            <!-- <div>
                                <span class="text-sm text-text-muted dark:text-text-muted-dark">Generic Name</span>
                                <div class="font-medium text-text dark:text-text-dark">{{ $medicine->generic_name ?? '-' }}</div>
                            </div> -->
                            
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
                                    ₹{{ number_format((float) $medicine->mrp, 2) }}
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
    <x-filament-actions::modals />
</x-page-layout>
