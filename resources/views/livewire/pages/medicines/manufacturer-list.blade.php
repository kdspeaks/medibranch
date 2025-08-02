<x-page-layout title="Manufacturers">
    <x-slot name="actionButton">
        {{-- <x-ui.button icon="fas-plus" class="w-full" @click="$dispatch('create-permission')">
             Create Permission
         </x-ui.button> --}}
        {{ $this->createAction }}
    </x-slot>
    <div class="my-5">
        {{ $this->table }}
    </div>
    <x-filament-actions::modals />
</x-page-layout>
