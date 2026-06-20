<div x-data="{ open: false }">
    <div class="relative">
        <button @click="open = !open"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-text bg-surface border border-border rounded-lg shadow-md hover:bg-surface/80 focus:outline-hidden dark:bg-surface-dark dark:text-text-dark dark:border-border-dark">
            <x-icon name="heroicon-o-language" class="w-4 h-4 mr-2" /> {{ strtoupper(app()->getLocale()) }}
            <x-icon name="heroicon-o-chevron-down" class="w-3 h-3 ml-2" x-show="!open" />
            <x-icon name="heroicon-o-chevron-up" class="w-3 h-3 ml-2" x-show="open" />
        </button>

        <div x-show="open" @click.away="open = false" x-transition
            class="absolute bottom-full right-0 mt-2 w-40 bg-surface dark:bg-surface-dark rounded-lg shadow-lg ring-1 ring-border dark:ring-border-dark">
            <a href="{{ url('/lang/en') }}" wire:navigate
                class="block px-4 py-2 hover:bg-surface-dark/10 dark:hover:bg-surface/10 font-sans text-xs {{ app()->getLocale() === 'en' ? 'text-button-bg dark:text-button-bg-dark' : 'text-text dark:text-text-dark' }}">
                <x-icon name="heroicon-o-flag" class="w-4 h-4 mr-2" /> English
            </a>
            <a href="{{ url('/lang/bn') }}" wire:navigate
                class="block px-4 py-2 hover:bg-surface-dark/10 dark:hover:bg-surface/10 font-bengali text-xs {{ app()->getLocale() === 'bn' ? 'text-button-bg dark:text-button-bg-dark' : 'text-text dark:text-text-dark' }}">
                <x-icon name="heroicon-o-flag" class="w-4 h-4 mr-2" /> বাংলা
            </a>
        </div>
    </div>
</div>
