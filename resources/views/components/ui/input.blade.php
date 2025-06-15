@props([
    'id' => null,
    'name',
    'type' => 'text',
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'icon' => null,
    'iconRight' => null,
    'autocomplete' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
])

@php
    $isPassword = $type === 'password';
    $inputId = $id ?? $name;
@endphp

<div class="space-y-1">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-text dark:text-text-dark">
            {{ $label }}
        </label>
    @endif

    <div class="relative" x-data="{ show: false }">
        @if ($icon)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="{{ $icon }} text-input-placeholder dark:text-input-placeholder text-sm"></i>
            </div>
        @endif

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            @if (!$isPassword)
                type="{{ $type }}"
            @else
                :type="show ? 'text' : 'password'"
            @endif
            value="{{ old($name, $value) }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-lg border ' .
                    ($icon ? 'pl-10 ' : '') .
                    ($isPassword ? 'pr-10 ' : '') .
                    ($error
                        ? 'border-error bg-red-50 text-error placeholder-error dark:bg-red-950 dark:border-error dark:text-error dark:placeholder-error'
                        : 'bg-input-bg text-input-text placeholder-input-placeholder border-input-border dark:bg-input-bg-dark dark:text-input-text-dark dark:placeholder-input-placeholder dark:border-input-border-dark'
                    ) .
                    ' sm:text-sm focus:ring-primary focus:border-primary'
            ]) }}
            placeholder="{{ $placeholder }}"
        >

        @if ($isPassword)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-input-placeholder dark:text-input-placeholder" @click="show = !show">
                <i x-show="!show" class="fas fa-eye text-sm"></i>
                <i x-show="show" class="fas fa-eye-slash text-sm"></i>
            </div>
        @endif
    </div>
    @if ($error)
        <p class="mt-2 text-sm text-error">{{ $error }}</p>
    @endif
</div>
