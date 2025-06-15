@props([
    'type' => 'button',
    'variant' => 'primary',
    'target' => null,
    'fullWidth' => false,
    'test' => false,
    'payload' => []
])

@php
    $base = 'inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition focus:ring-4';
    $variants = [
        'primary' =>
            'bg-button-bg text-button-text hover:bg-primary/90 dark:bg-button-bg-dark dark:text-button-text-dark focus:ring-primary/30 dark:focus:ring-primary-dark/30',
        'secondary' =>
            'bg-secondary text-white hover:bg-secondary/80 dark:bg-secondary-dark focus:ring-secondary/30 dark:focus:ring-secondary-dark/30',
        'danger' =>
            'bg-error text-white hover:bg-error/90 dark:bg-error-dark focus:ring-error/30 dark:focus:ring-error-dark/30',
        'outline' =>
            'border border-border text-text hover:bg-border dark:text-text-dark dark:border-border-dark dark:hover:bg-border-dark focus:ring-border/30 dark:focus-ring-border-dark/30',
    ];
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
    if ($fullWidth) {
        $classes .= ' w-full';
    }
    $wireAction = $attributes->get('wire:click') ?? ($attributes->get('wire:submit') ?? null);
    $wireTarget = $target ?? $wireAction;

@endphp

<button type="{{ $type }}" wire:loading.attr="disabled"
    {{ $attributes->merge(['class' => "$classes disabled:opacity-70 disabled:cursor-not-allowed"]) }}>
    @if ($wireTarget)
        <div wire:loading.class.remove="hidden" wire:target="{{ $wireTarget }}" class="items-center gap-2 hidden flex">
            <svg class="w-4 h-4 mr-2 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ __('messages.loading') }}
        </div>
    @endif

    <span @if ($wireTarget) wire:loading.remove wire:target="{{ $wireTarget }}" @endif>
        {{ $slot }}
    </span>
</button>
