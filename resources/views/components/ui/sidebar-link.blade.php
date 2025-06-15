@props(['route', 'icon' => null, 'badge' => null])

@php
    $href = Route::has($route) ? route($route) : '/' . $route;
    // $addWireNavigate = Route::has($route) ? true : false;
    $active = request()->routeIs($route) || request()->is($route . '/*') ? true : false;
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->except(['class']) }}
    {{ $attributes->merge([
        'class' =>
            'flex items-center p-2 text-base font-normal rounded-lg transition duration-75 group ' .
            ($active
                ? 'text-text bg-surface-dark/10 dark:text-text-dark dark:bg-surface/10'
                : 'text-text hover:bg-surface-dark/10 dark:text-text-dark dark:hover:bg-surface/10'),
    ]) }}>

    @if ($icon)
        <x-icon :name="$icon"
            {{ $attributes->merge([
                'class' =>
                    'w-5 h-5 group-hover:text-text dark:group-hover:text-text-dark ' .
                    ($active
                        ? 'text-text dark:text-text-dark'
                        : 'text-text-muted dark:text-text-muted-dark'),
            ]) }} />
    @endif

    <span class="flex-1 ml-3 whitespace-nowrap leading-tight" sidebar-toggle-item>{{ $slot }}</span>

    @if ($badge)
        <span
            class="inline-flex justify-center items-center p-1 ml-3 w-5 h-5 text-sm font-medium rounded-full text-primary-800 bg-primary-100"
            sidebar-toggle-item>
            {{ $badge }}
        </span>
    @endif
</a>
