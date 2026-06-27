<x-page-layout title="{{ __('messages.sales_history') }}">
    <x-slot name="actionButton">
        <x-filament::button wire:navigate href="{{route('pos')}}" tag="a" color="success" icon="heroicon-o-shopping-bag">
            {{ __('messages.pos') }}
        </x-filament::button>
    </x-slot>
    <div class="my-5">
        {{ $this->table }}
    </div>
    <x-filament-actions::modals />
</x-page-layout>
