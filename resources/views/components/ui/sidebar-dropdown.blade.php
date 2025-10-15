@props(['title', 'icon' => null, 'id' => 'dropdown-' . Str::slug($title)])
@php
    $slotHtml = trim($slot);
    preg_match_all('/href="([^"]+)"/', $slotHtml, $matches);
    $subLinks = $matches[1] ?? [];

    $isActive = collect($subLinks)->contains(function ($link) {
        return request()->is(trim(parse_url($link, PHP_URL_PATH), '/'));
    });
@endphp
<li {{-- x-data="{ isExpanded: {{ json_encode((bool) $isActive) }} }" --}} x-data="{
    isExpanded: false,
    test: 'kunal',
    checkActive() {
        const current = window.location.pathname.replace(/\/+$/, '');

        // Find all child links inside this dropdown
        const links = this.$el.querySelectorAll('a');
        this.isExpanded = Array.from(links).some(link => {
            const target = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '');
            return current === target || current.startsWith(target + '/');
        });
    },
    toggleExpanded() {
        this.isExpanded = !this.isExpanded;
    }
}" {{-- x-init="checkActive()" --}} {{-- @popstate.window="checkActive()" updates on browser navigation --}}>
    <button type="button" x-on:click="isExpanded = !isExpanded"
        class="flex items-center p-2 w-full text-base font-normal text-text rounded-lg transition duration-75 group hover:bg-surface-dark/10 dark:text-text-dark dark:hover:bg-surface/10" ">

         @if ($icon)
        <x-icon :name="$icon"
            class="shrink-0 w-5 h-5 text-text-muted transition duration-75 group-hover:text-text dark:text-text-muted-dark dark:group-hover:text-text-dark" />
        @endif

        <span class="flex-1 ml-4 text-left whitespace-nowrap" sidebar-toggle-item>{{ $title }}</span>

        <x-icon name="fwb-o-angle-down" class="w-3 h-3 transition-transform duration-200 transform"
            x-bind:class="isExpanded ? 'rotate-180' : ''" />
    </button>

    <ul id="{{ $id }}" :class="isExpanded ? '' : 'hidden'" class="py-2 space-y-2">
        {{ $slot }}
    </ul>
</li>
