<x-page-layout>
    <x-slot name="actionButton">
        {{-- <x-ui.button icon="fas-plus" class="w-full" @click="$dispatch('create-permission')">
             Create Permission
         </x-ui.button> --}}
        {{ $this->createAction }}
    </x-slot>

    <div class="md:mt-5">
        <livewire:filament-permission-table />
    </div>
    <livewire:components.ui.modal.create-permission />
    <livewire:components.ui.modal.delete />


    <x-filament-actions::modals />
</x-page-layout>
