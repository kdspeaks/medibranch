<x-page-layout title="{{ __('messages.customers') }}">
    <x-slot name="actionButton">
        {{ $this->createAction }}
    </x-slot>
    <div class="my-5">
        {{ $this->table }}
    </div>
    <x-filament-actions::modals />
</x-page-layout>
