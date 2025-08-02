<x-page-layout title="Medicine View {{ $medicine->name }}">
    <x-slot name="actionButton">
        <x-ui.button icon="fas-plus" variant="primary" class="w-full" wire:navigate href="{{ route('medicines.edit', ['medicine' => $this->medicine ]) }}">
             Edit Medicine
         </x-ui.button>
    </x-slot>
    <div class="my-5">
        <div class="space-y-6">

            <x-filament::card>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Name</p>
                        <p class="font-medium text-text dark:text-text-dark">{{ $medicine->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Potency</p>
                        <p class="font-medium text-text dark:text-text-dark">{{ $medicine->potency }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Form</p>
                        <p class="font-medium text-text dark:text-text-dark">{{ $medicine->form }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Packing</p>
                        <p class="font-medium text-text dark:text-text-dark">{{ $medicine->packing_quantity }} {{ $medicine->packing_unit }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Barcode</p>
                        <p class="font-mono text-text dark:text-text-dark">{{ $medicine->barcode }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">SKU</p>
                        <p class="font-mono text-text dark:text-text-dark">{{ $medicine->sku }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Purchase Price</p>
                        <p class="font-medium text-success dark:text-success-dark">
                            ₹{{ number_format($medicine->purchase_price, 2) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Selling Price</p>
                        <p class="font-medium text-info dark:text-info-dark">
                            ₹{{ number_format($medicine->selling_price, 2) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Manufacturer</p>
                        <p class="font-medium text-text dark:text-text-dark">{{ $medicine->manufacturer?->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Status</p>
                        <p class="font-medium text-text dark:text-text-dark">
                            <span @class([
                                'text-success dark:text-success-dark' => $medicine->is_active,
                                'text-error dark:text-error-dark' => !$medicine->is_active,
                            ])>
                                {{ $medicine->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>

                    <div class="sm:col-span-3">
                        <p class="text-sm text-text-muted dark:text-text-muted-dark">Description</p>
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $medicine->description ?? '<p class="text-gray-400">No description provided.</p>' !!}
                        </div>
                    </div>
                </div>
            </x-filament::card>

           

        </div>
        
    </div>
    <script>
        window.addEventListener('scroll-to-top', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>

</x-page-layout>
