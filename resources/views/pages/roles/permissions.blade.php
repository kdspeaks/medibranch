<x-app-layout title="Permissions" :hasPadding="false">
    <x-slot name="actionButton">
        <x-ui.button icon="fas-plus" class="w-full" @click="$dispatch('create-permission')">
            Create Permission
        </x-ui.button>
    </x-slot>
    <div class="md:mt-5">
        <livewire:permission-table />
    </div>
    <livewire:components.ui.modal.create-permission />
    <livewire:components.ui.modal.delete />
</x-app-layout>
