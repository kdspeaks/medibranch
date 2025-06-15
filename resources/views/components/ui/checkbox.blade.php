@props([
    'id' => Str::uuid(), // Fallback ID
    'label' => '',
    'name' => '',
    'checked' => false,
    'disabled' => false,
])

<div class="flex items-center gap-2">
    <label for="{{ $id }}" class="inline-flex relative items-center cursor-pointer">
        <input type="checkbox"
            name="{{ $name }}"
            id="{{ $id }}"
            value="1"
            @checked($checked)
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'sr-only peer']) }}>

        <div class="w-11 h-6 rounded-full bg-border peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary dark:bg-border-dark peer-checked:bg-primary peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border border-gray-300 dark:border-gray-600 after:rounded-full after:h-5 after:w-5 after:transition-all">
        </div>
    </label>

    @if ($label)
        <label for="{{ $id }}" class="text-sm text-text dark:text-text-dark">{{ $label }}</label>
    @endif
</div>

