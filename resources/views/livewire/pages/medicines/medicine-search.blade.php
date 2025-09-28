<div x-data>
    {{-- Search / Barcode input --}}
    <x-ui.input id="barcode-input" name="barcode" type="text" icon="fas fa-barcode" wire:model.live.debounce.300ms="query"
        wire:keydown.enter.prevent="$parent.addByBarcode($event.target.value)" placeholder="Search or scan barcode..."
        autocomplete="off" class="h-12 !bg-white dark:!bg-[#35383E] shadow" />

    {{-- <x-filament::input.wrapper prefix-icon="heroicon-m-check-circle" prefix-icon-color="success">
        <x-filament::input id="barcode-input" name="barcode" wire:model.live.debounce.300ms="query" wire:keydown.enter.prevent="$parent.addByBarcode($event.target.value)" placeholder="Search or scan barcode..." />
    </x-filament::input.wrapper> --}}

    {{-- Search dropdown results --}}
    @if (!empty($results))
        <div x-on:clear-results.window="
            $el.closest('.z-50')?.classList.add('hidden');
            document.getElementById('barcode-input').value = '';
            document.getElementById('barcode-input').focus();
            console.log('Cleared results for', $event.detail.code);
        "
            class="z-50 mt-1 mb-4 text-base list-none bg-surface divide-y divide-border rounded-sm shadow-sm 
                   dark:bg-surface-dark border dark:divide-border/30 dark:border-border-dark/90 block">
            <ul x-data class="py-1" role="none">
                @foreach ($results as $medicine)
                    <li class="cursor-pointer px-4 py-2 text-sm text-text/80 hover:bg-surface-dark/10 
                               dark:text-text-dark/80 dark:hover:bg-surface/10 dark:hover:text-text-dark"
                        wire:click="$parent.addPurchaseItem({{ $medicine['id'] }})">
                        {{ is_array($medicine) ? $medicine['name'] : $medicine->name }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
