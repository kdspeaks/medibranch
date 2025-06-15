@props(['title', 'icon' => null, 'id' => 'dropdown-' . Str::slug($title)])
@php
    $slotHtml = trim($slot);
    preg_match_all('/href="([^"]+)"/', $slotHtml, $matches);
    $subLinks = $matches[1] ?? [];

    $isActive = collect($subLinks)->contains(function ($link) {
        return request()->is(trim(parse_url($link, PHP_URL_PATH), '/'));
    });
@endphp
<li>
    <button type="button"
        class="flex items-center p-2 w-full text-base font-normal text-text rounded-lg transition duration-75 group hover:bg-surface-dark/10 dark:text-text-dark dark:hover:bg-surface/10"
        aria-controls="{{ $id }}" data-collapse-toggle="{{ $id }}"
        aria-expanded="{{ $isActive ? 'true' : 'false' }}">

        @if ($icon)
            <x-icon :name="$icon"
                class="flex-shrink-0 w-5 h-5 text-text-muted transition duration-75 group-hover:text-text dark:text-text-muted-dark dark:group-hover:text-text-dark" />
        @endif

        <span class="flex-1 ml-4 text-left whitespace-nowrap" sidebar-toggle-item>{{ $title }}</span>

        <x-icon name="fwb-o-angle-down"
            class="w-3 h-3 transition-transform duration-200 transform group-aria-expanded:rotate-180"
            sidebar-toggle-item />
    </button>

    <ul id="{{ $id }}" class="{{ $isActive ? '' : 'hidden' }} py-2 space-y-2">
        {{ $slot }}
    </ul>
</li>
