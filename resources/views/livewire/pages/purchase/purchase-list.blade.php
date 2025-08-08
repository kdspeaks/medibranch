<x-page-layout title="Purchases">
    <x-slot name="actionButton">
        {{-- <x-ui.button icon="fas-plus" class="w-full" @click="$dispatch('create-permission')">
             Create Permission
         </x-ui.button> --}}
        <x-filament::button wire:navigate href="{{route('medicines.purchases.create')}}" tag="a">
            Create Purchase
        </x-filament::button>
    </x-slot>
    <div class="my-5">
        {{ $this->table }}
    </div>
    <x-filament-actions::modals />
</x-page-layout>
