<x-page-layout title="Medicine Forms & Units">
    <x-slot name="actionButton">
        {{ ($this->createAction)(['class' => 'w-full']) }}
    </x-slot>
    
    <div class="my-5">
        {{ $this->table }}
    </div>
    
    <x-filament-actions::modals />
</x-page-layout>
