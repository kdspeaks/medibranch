<div 
x-data="{ show: @entangle($attributes->wire('model'))}" x-show="show" x-cloak
{{-- wire:show="{{$attributes->wire('model')->value()}}" --}}
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-xl shadow-lg"
        @click.away="$wire.show = false" x-transition>
        <button class="absolute top-2 right-2 text-gray-600" @click="$wire.show = false">✖</button>

        {{-- Modal Content --}}

        {{ $slot }}

        {{-- End Modal Content --}}

    </div>
</div>
