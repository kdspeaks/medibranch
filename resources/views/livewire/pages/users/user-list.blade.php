<x-page-layout title="Users">
    <x-slot name="actionButton">
        {{-- <x-ui.button icon="heroicon-o-plus" class="w-full" @click="$dispatch('create-permission')">
             Create Permission
         </x-ui.button> --}}
        {{ $this->createAction }}
    </x-slot>
    <div class="my-5">
        {{-- <livewire:pages.users.user-table /> --}}
        {{ $this->table }}
    </div>
    <livewire:components.ui.modal.change-role />
</x-page-layout>
