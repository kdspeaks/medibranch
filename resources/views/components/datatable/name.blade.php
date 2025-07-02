@php
    $name = $getRecord()->name;
    $colors = [
        'bg-red-500',
        'bg-green-500',
        'bg-blue-500',
        'bg-yellow-500',
        'bg-indigo-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-teal-500',
        'bg-amber-500',
        'bg-lime-500',
        'bg-cyan-500',
    ];
@endphp
{{-- <div class="flex flex-col">
    <div x-data="avatarComponent(@js($name), @js($colors))">
        <button type="button"
            class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full focus:ring-4 focus:ring-primary/30 dark:focus:ring-primary/50"
            :class="bgColor">
            <span class="sr-only">Open user menu</span>
            <span x-text="initials"></span>
        </button>
    </div>
    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $name }}</span>
    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $email }}</span>
</div> --}}
<div class="flex items-center space-x-2 whitespace-nowrap lg:mr-0">
    <div x-data="avatarComponent(@js($name), @js($colors))">
        <div class="flex items-center justify-center w-6 h-6 text-white rounded-full text-xxs" :class="bgColor">
            <span x-text="initials"></span>
        </div>
    </div>
    <div class="text-sm font-semibold text-text dark:text-text-dark">{{ $name }}</div>
</div>
