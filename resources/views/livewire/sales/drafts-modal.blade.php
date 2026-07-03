<div>
    @if($drafts->isEmpty())
        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
            No saved drafts found.
        </div>
    @else
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($drafts as $draft)
                <div class="py-4 flex items-center justify-between" wire:key="draft-{{ $draft->id }}">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $draft->reference_name ?? 'Draft #' . $draft->id }}
                        </h4>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex space-x-4">
                            <span>{{ $draft->created_at->format('d M Y, h:i A') }}</span>
                            <span>Items: {{ collect($draft->cart_data['cart'] ?? [])->sum('quantity') }}</span>
                            <span class="font-medium text-primary-600">₹{{ number_format($draft->total_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-filament::button size="sm" color="danger" variant="text" wire:click="deleteDraft({{ $draft->id }})">
                            Delete
                        </x-filament::button>
                        <x-filament::button size="sm" wire:click="loadDraft({{ $draft->id }})">
                            Load
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
