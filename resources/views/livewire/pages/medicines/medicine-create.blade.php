<x-page-layout title="Create Medicine">
    <x-slot name="actionButton">
        <x-ui.button icon="fas-plus" variant="outline" class="w-full" wire:navigate href="{{ route('medicines.list') }}">
             Back to Medicines
         </x-ui.button>
    </x-slot>
    <div class="my-5">
        <form wire:submit.prevent="submit" id="create-medicine-form">
            {{ $this->form }}
            <div class="flex gap-2">
                <x-filament::button type="submit" class="mt-4" wire:target="submit">Save Medicine</x-filament::button>
                <x-filament::button class="mt-4" wire:click="submitAndCreate">Save & Create another</x-filament::button>
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

</x-page-layout>
