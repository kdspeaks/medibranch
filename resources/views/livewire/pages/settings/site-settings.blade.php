<x-page-layout title="Site Settings">
    <form wire:submit.prevent="save" class="space-y-6 bg-surface dark:bg-surface-dark p-6 rounded-lg mt-5 border border-border dark:border-border-dark">
        {{ $this->form }}
        <x-filament::button type="submit" wire:target="save">Save</x-filament::button>
    </form>
</x-page-layout>