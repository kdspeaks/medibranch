@php
    $record = $getRecord();
    $name = $record->name;
    $sku = $record->sku;
    $potency = $record->potency;
    $packingQty = $record->packing_quantity;
    $unit = $record->medicineUnit?->name;
    $manufacturer = $record->manufacturer;

    $brandColorPalettes = [
        'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700',
        'bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700',
        'bg-purple-100 text-purple-700 border-purple-300 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-700',
        'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700',
        'bg-rose-100 text-rose-700 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700',
        'bg-cyan-100 text-cyan-700 border-cyan-300 dark:bg-cyan-900/40 dark:text-cyan-300 dark:border-cyan-700',
        'bg-indigo-100 text-indigo-700 border-indigo-300 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-700',
        'bg-teal-100 text-teal-700 border-teal-300 dark:bg-teal-900/40 dark:text-teal-300 dark:border-teal-700',
        'bg-fuchsia-100 text-fuchsia-700 border-fuchsia-300 dark:bg-fuchsia-900/40 dark:text-fuchsia-300 dark:border-fuchsia-700',
        'bg-orange-100 text-orange-700 border-orange-300 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-700',
    ];
    $colorClass = $manufacturer 
        ? $brandColorPalettes[abs(crc32((string) ($manufacturer->id ?? $manufacturer->name))) % count($brandColorPalettes)] 
        : '';
    $initials = $manufacturer 
        ? collect(explode(' ', trim($manufacturer->name)))->filter()->take(2)->map(fn($p) => strtoupper(substr($p, 0, 1)))->implode('') 
        : '';
@endphp
<div class="flex items-center gap-3 px-3 py-1.5">
    {{-- Row Icon: Manufacturer Logo or Unique Colored Initial Avatar --}}
    @if($manufacturer?->logo)
        <img src="{{ Storage::disk('public')->url($manufacturer->logo) }}" 
             alt="{{ $manufacturer->name }}" 
             title="{{ $manufacturer->name }}"
             class="w-9 h-9 rounded-full object-cover border border-border dark:border-border-dark bg-white shrink-0 shadow-xs">
    @elseif($manufacturer)
        <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border shrink-0 shadow-xs {{ $colorClass }}"
              title="{{ $manufacturer->name }}">
            {{ $initials ?: strtoupper(substr($manufacturer->name, 0, 1)) }}
        </span>
    @else
        <span class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs shrink-0 border border-border dark:border-border-dark">
            <x-heroicon-o-beaker class="w-4 h-4" />
        </span>
    @endif

    {{-- Medicine Details --}}
    <div class="flex flex-col gap-0.5 min-w-0">
        {{-- Top Line: Medicine Name --}}
        <div class="text-sm font-semibold text-text dark:text-text-dark">
            {{ $name }}
        </div>

        {{-- Middle Line: Potency and Qty on the same line --}}
        @if($potency || $packingQty)
            <div class="flex items-center gap-1.5 flex-wrap">
                @if($potency)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-primary/10 text-primary dark:bg-primary-dark/20 dark:text-primary-dark border border-primary/20">
                        {{ $potency }}
                    </span>
                @endif

                @if($packingQty)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-bold bg-amber-500/10 text-amber-700 dark:bg-amber-400/20 dark:text-amber-300 border border-amber-500/30">
                        {{ $packingQty }}{{ $unit ? ' ' . $unit : '' }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Bottom Line: SKU --}}
        @if($sku)
            <div class="text-xs text-text-muted dark:text-text-muted-dark">
                <span class="text-[10px] uppercase font-semibold text-text-muted/70">SKU:</span> {{ $sku }}
            </div>
        @endif
    </div>
</div>