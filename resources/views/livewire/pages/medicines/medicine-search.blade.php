<div x-data="{ 
         highlightedIndex: 0,
         focusNext() {
             let count = this.$el.querySelectorAll('.search-item').length;
             if (this.highlightedIndex < count - 1) this.highlightedIndex++;
         },
         focusPrev() {
             if (this.highlightedIndex > 0) this.highlightedIndex--;
         },
         selectItem() {
             let items = this.$el.querySelectorAll('.search-item');
             if (items.length > 0 && items[this.highlightedIndex]) {
                 items[this.highlightedIndex].click();
             } else {
                 let input = document.getElementById('barcode-input');
                 if(input && input.value) {
                     $wire.$parent.addByBarcode(input.value);
                 }
             }
         }
     }"
     @keydown.arrow-down.prevent="focusNext()"
     @keydown.arrow-up.prevent="focusPrev()"
     @keydown.enter.prevent="selectItem()">
    
    {{-- Search / Barcode input --}}
    <x-ui.input id="barcode-input" name="barcode" type="text" icon="heroicon-o-qr-code" wire:model.live.debounce.500ms="query"
        placeholder="Search or scan barcode..."
        autocomplete="off" class="h-12 !bg-white dark:!bg-[#35383E] shadow" />

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
            <ul class="py-1" role="none">
                @foreach ($results as $medicine)
                    <li class="search-item cursor-pointer px-4 py-2 text-sm text-text/80 hover:bg-surface-dark/10 
                               dark:text-text-dark/80 dark:hover:bg-surface/10 dark:hover:text-text-dark transition-colors"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': highlightedIndex === {{ $loop->index }} }"
                        wire:click="$parent.addPurchaseItem({{ $medicine['id'] }})">
                        {{ is_array($medicine) ? $medicine['name'] : $medicine->name }}
                        @if(count($results) === 1)
                            <span class="ml-2 text-xs text-blue-600 dark:text-blue-400 font-normal bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 px-1.5 py-0.5 rounded shadow-sm">(Enter ↵)</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
