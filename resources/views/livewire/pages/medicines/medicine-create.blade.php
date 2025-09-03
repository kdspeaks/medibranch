<x-page-layout title="Create Medicine">
    <x-slot name="actionButton">
        <x-ui.button icon="fas-plus" variant="outline" class="w-full" wire:navigate href="{{ route('medicines.list') }}">
            Back to Medicines
        </x-ui.button>
    </x-slot>
    <div class="my-5" {{-- x-data --}} {{-- x-on:ui:medicine-selected.window="
            // $event.detail has: id, name, purchase_price, selling_price
            $wire.call('onMedicineSelectedFromJs', $event.detail.id)
        " --}}
        x-on:medicine-selected.window="
            alert('ok')
        ">
        <form wire:submit.prevent="formSubmit" id="create-medicine-form">
            {{ $this->form }}
            <div class="flex gap-2">
                <x-filament::button type="submit" class="mt-4" wire:target="formSubmit">Save
                    Medicine</x-filament::button>
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
    </script>
    <x-filament-actions::modals />
</x-page-layout>
