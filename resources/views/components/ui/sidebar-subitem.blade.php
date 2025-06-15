@php
    $href = Route::has($route) ? route($route) : '/'.$route;
    $active = request()->routeIs($route) || request()->is($route . '/*')  ? true : false;
@endphp

<li>
    <a href="{{ $href }}" wire:navigate
       {{ $attributes->merge(['class' => (
            'flex items-center p-2 pl-11 text-base rounded-lg transition group ' .
            ($active
                ? 'text-primary bg-surface-dark/10 dark:text-primary-dark dark:bg-surface/10'
                : 'text-text hover:bg-surface-dark/10 dark:text-text-dark dark:hover:bg-surface/10')
        )]) }}>{{ trim($slot) }}</a>
</li>
