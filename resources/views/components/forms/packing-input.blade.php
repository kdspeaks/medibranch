<div class="flex space-x-2">
    <input
        type="number"
        min="1"
        wire:model.defer="{{ $getStatePath() }}.quantity"
        class="filament-input w-full rounded-r-none"
        placeholder="Qty"
    >

    <select
        wire:model.defer="{{ $getStatePath() }}.unit"
        class="filament-input w-full rounded-l-none"
    >
        @foreach ($units as $unit)
            <option value="{{ $unit }}">{{ $unit }}</option>
        @endforeach
    </select>
</div>
