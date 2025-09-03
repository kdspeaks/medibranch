<div>
    <input type="text" wire:model.live.debounce.300ms="query" class="border p-2 w-full rounded" placeholder="Search medicine...">

    @if(!empty($results))
        <ul class="border mt-2 rounded bg-white">
            @foreach($results as $medicine)
                <li class="p-2 border-b last:border-none cursor-pointer hover:bg-gray-100"
                    wire:click="$parent.addPurchaseItem({{ $medicine['id'] }})"
                    {{-- @click="$dispatch('medicine-selected')->to(App)" --}}
                    {{-- @click="$dispatch('medicine-selected')" --}}
                    >
                    {{ is_array($medicine) ? $medicine['name'] : $medicine->name }}
                </li>
            @endforeach
        </ul>
    @endif
    
</div>

