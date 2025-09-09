<div {{ $attributes->merge() }}>
    <div class="relative">
        <!-- Button with Flowbite tooltip trigger -->
        <button
            @click="toggleTheme"
            type="button"
            data-tooltip-target="tooltip-theme"
            data-tooltip-placement="bottom"
            class="flex items-center justify-center w-8 h-8 rounded-full transition-all duration-300 ease-in-out focus:ring-4"
            :class="{
                'bg-warning focus:ring-warning/30': !dark,
                'bg-primary focus:ring-primary/30': dark,
                'ring-4 ring-primary/30 shadow-xl shadow-primary/40': animating,
                'hover:scale-105': true
            }"
        >
            <template x-if="!dark">
                <i class="fas fa-sun text-surface text-sm relative z-10 transition-colors duration-300"></i>
            </template>
            <template x-if="dark">
                <i class="fas fa-moon text-text-muted-dark text-sm relative z-10 transition-colors duration-300"></i>
            </template>
            <span class="sr-only">Toggle Theme</span>
        </button>

        <!-- Flowbite Tooltip -->
        <div id="tooltip-theme" role="tooltip"
            class="absolute z-50 invisible inline-block px-2 py-1 text-xs font-medium text-text transition-opacity bg-surface rounded-lg shadow-xs opacity-0 tooltip dark:bg-surface-dark dark:text-text-dark whitespace-nowrap">
            <span x-text="dark ? '{{ __('messages.light_mode') }}' : '{{ __('messages.dark_mode') }}'"></span>
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </div>
</div>

<div x-show="animating" x-transition:enter="transition ease-out duration-1000"
    x-transition:enter-start="scale-0 opacity-20" x-transition:enter-end="scale-[100] opacity-20"
    class="absolute opacity-0 z-40 top-3 right-3 w-10 h-10 dark:bg-background bg-background-dark rounded-full origin-center transform"
    style="pointer-events: none;">
</div>

