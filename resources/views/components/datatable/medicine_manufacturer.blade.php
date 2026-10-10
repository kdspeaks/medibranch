@php
    $record = $getRecord();
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
@if($manufacturer)
    <div class="flex items-center gap-2 px-3 whitespace-nowrap">
        @if($manufacturer->logo)
            <img src="{{ Storage::disk('public')->url($manufacturer->logo) }}" 
                 alt="{{ $manufacturer->name }}" 
                 class="w-6 h-6 rounded-full object-cover border border-border dark:border-border-dark bg-white shrink-0">
        @else
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border shrink-0 {{ $colorClass }}"
                  title="{{ $manufacturer->name }}">
                {{ $initials ?: strtoupper(substr($manufacturer->name, 0, 1)) }}
            </span>
        @endif
        <span class="text-sm text-text dark:text-text-dark font-medium">{{ $manufacturer->name }}</span>
    </div>
@else
    <span class="text-sm text-text-muted dark:text-text-muted-dark px-3">-</span>
@endif
