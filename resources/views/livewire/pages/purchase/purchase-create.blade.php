<x-page-layout title="Create Purchase">
    <x-slot name="actionButton">
        <x-ui.button icon="heroicon-o-plus" variant="outline" class="w-full" wire:navigate
            href="{{ route('medicines.purchases.list') }}">
            Back to Purchases
        </x-ui.button>
    </x-slot>
    <div class="my-5">
        <form wire:submit.prevent="formSubmit" id="create-purchase-form" 
        {{-- x-data="purchaseForm()" x-init="init()" --}}
        >
            {{ $this->form }}
            <div class="flex gap-2">
                <x-filament::button type="submit" class="mt-4" wire:target="formSubmit">Save
                    Purchase</x-filament::button>
                <x-filament::button class="mt-4" wire:click="submitAndCreate">Save & Create
                    another</x-filament::button>
                <x-filament::button class="mt-4" color="danger" wire:click="resetForm">Reset</x-filament::button>
            </div>
        </form>
    </div>
    <script>
        window.addEventListener('scroll-to-top', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        function purchaseForm() {
    return {
        // Entangle the Filament form items array with Alpine (live two-way)
        items: @entangle('form.items'),

        // Load tax rates map from backend (id => rate). Adjust as needed.
        taxRates: @json(\App\Models\Tax::pluck('rate', 'id')->map(fn($v)=> (float)$v)),

        init() {
            // Deep watch for changes in items (quantity, unit_purchase_price, tax_id, etc)
            this.$watch('items', (items) => {
                // total in paise (integer) to avoid float imprecision
                let totalPaise = 0;

                if (! Array.isArray(items)) {
                    $wire.set('form.total_amount', 0.00);
                    return;
                }

                items.forEach((item, idx) => {
                    const qty = Number(item?.quantity) || 0;
                    const unitPurchase = Number(item?.unit_purchase_price) || 0;
                    const taxId = item?.tax_id ?? null;

                    if (qty <= 0 || unitPurchase <= 0) {
                        // ensure server side fields are zeroed
                        $wire.set(`form.items.${idx}.tax_amount`, 0.00);
                        $wire.set(`form.items.${idx}.line_total_amount`, 0.00);
                        return;
                    }

                    // get tax rate (percentage) from the taxRates map
                    const taxRate = (taxId && this.taxRates.hasOwnProperty(taxId)) ? Number(this.taxRates[taxId]) : 0.0;

                    // compute: line = qty * unitPurchase
                    const line = qty * unitPurchase;
                    const taxAmount = (taxRate > 0) ? (line * (taxRate / 100.0)) : 0.0;
                    const lineWithTax = line + taxAmount;

                    // update item fields on Livewire (2 decimal places)
                    $wire.set(`form.items.${idx}.tax_amount`, parseFloat(taxAmount.toFixed(2)));
                    $wire.set(`form.items.${idx}.line_total_amount`, parseFloat(lineWithTax.toFixed(2)));

                    // add to total (in paise)
                    totalPaise += Math.round(lineWithTax * 100);
                });

                // set aggregated total back to Livewire (two decimals)
                const total = +(totalPaise / 100).toFixed(2);
                $wire.set('form.total_amount', total);
            }, { deep: true });
        }
    };
}
    </script>
    <x-filament-actions::modals />
</x-page-layout>
