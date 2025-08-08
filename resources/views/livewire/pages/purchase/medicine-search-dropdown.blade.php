<div x-data="{
    open: false,
    selectedIndex: -1,
    init() {
        this.$watch('open', (val) => {
            if (!val) this.selectedIndex = -1;
        });
    },
    select(index) {
        if (!Array.isArray($wire.medicineSuggestions) || index < 0 || index >= $wire.medicineSuggestions.length) {
            return;
        }
        const id = $wire.medicineSuggestions[index].id;
        $wire.selectMedicineFromSearch(id);
        this.open = false;
    }

}" x-init="init()" class="relative">
    <input type="text" wire:model.debounce.300ms="medicineSearch" x-ref="searchInput" @focus="open = true"
        @keydown.arrow-down.prevent="selectedIndex = (selectedIndex + 1) % $wire.medicineSuggestions.length"
        @keydown.arrow-up.prevent="selectedIndex = (selectedIndex - 1 + $wire.medicineSuggestions.length) % $wire.medicineSuggestions.length"
        @keydown.enter.prevent="select(selectedIndex)" placeholder="Search or scan product..."
        class="w-full px-4 py-2 border border-gray-300 rounded-md" autocomplete="off" />


    <ul x-show="open && $wire.medicineSuggestions.length > 0" @click.away="open = false"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
        <template x-for="(suggestion, index) in $wire.medicineSuggestions" :key="suggestion.id">
            <li :class="selectedIndex === index ? 'bg-blue-100' : ''" class="px-4 py-2 cursor-pointer hover:bg-blue-50"
                @click="select(index)">
                <span x-text="suggestion.name"></span>
                <span class="text-xs text-gray-500" x-text="' (Barcode: ' + suggestion.barcode + ')'"></span>
            </li>
        </template>
    </ul>
</div>
