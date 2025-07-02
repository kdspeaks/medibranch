<x-page-layout title="Roles">
    <x-slot name="actionButton">
        {{-- <x-ui.button icon="fas-plus" class="w-full" @click="$dispatch('create-permission')">
             Create Permission
         </x-ui.button> --}}
        {{ $this->createAction }}
    </x-slot>
    <div class="md:mt-5">
        {{ $this->table }}
    </div>
    <x-filament-actions::modals />
</x-page-layout>
