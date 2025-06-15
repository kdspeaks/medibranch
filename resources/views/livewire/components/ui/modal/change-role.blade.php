<x-base-modal wire:model="show">
    <div>
        <form wire:submit="updateRole" class="w-full mx-auto space-y-3">
            <label for="roleSelect" class="block text-sm font-medium text-gray-900 dark:text-white">
                Select Role for {{ $user->name ?? 'User' }}
            </label>

            <select id="roleSelect" wire:model="selectedRole" required
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                   focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5
                   dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                   dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                <option value="" disabled>Select a role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            {{-- <button type="submit" wire:loading.class="hidden" wire:target="updateRole">Save</button>

            <div wire:loading.class.remove="hidden" wire:target="updateRole" class="text-text hidden">
                Saving post...
            </div> --}}
            <x-ui.button type="submit" target="updateRole">
                Update Role
            </x-ui.button>
        </form>
    </div>
</x-base-modal>
