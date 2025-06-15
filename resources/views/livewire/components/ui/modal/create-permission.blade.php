<x-base-modal wire:model="show">
    <div>
        <form wire:submit="save" class="w-full mx-auto space-y-3">
            <x-ui.input label="{{ __('Permission Name') }}" name="name" type="text" wire:model="name"
                placeholder="Permission Name" :error="$errors->first('name')" />

            <x-ui.button type="submit" target="save">
                Save
            </x-ui.button>
        </form>
    </div>
</x-base-modal>

