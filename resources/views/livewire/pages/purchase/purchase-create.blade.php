<x-page-layout title="Create Purchase">
    <x-slot name="actionButton">
        <x-ui.button icon="fas-plus" variant="outline" class="w-full" wire:navigate href="{{ route('medicines.purchases.list') }}">
            Back to Purchases
        </x-ui.button>
    </x-slot>
    <div class="my-5">
        <form wire:submit.prevent="formSubmit" id="create-purchase-form">
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

