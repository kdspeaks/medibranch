<div class="flex h-screen overflow-hidden bg-background dark:bg-background-dark text-text dark:text-text-dark"
    x-data
    @keydown.window.prevent.f2="document.getElementById('search').focus()"
    @keydown.window.prevent.f4="$wire.count($wire.cart) > 0 ? $wire.set('showCheckoutModal', true) : null"
    @keydown.window.prevent.f8="document.getElementById('customerSelect').focus()"
>
    <!-- Left Pane: Product Search and Selection -->
    <div class="flex flex-col flex-1 border-r border-border dark:border-border-dark">
        <!-- Header / Search -->
        <div class="p-4 bg-surface dark:bg-surface-dark border-b border-border dark:border-border-dark flex items-center justify-between">
            <div class="flex items-center gap-4 w-full max-w-2xl">
                <a href="{{ route('dashboard') }}" class="text-text-muted hover:text-text dark:text-text-dark/70 dark:hover:text-text-dark transition">
                    <x-heroicon-o-arrow-left class="w-6 h-6" />
                </a>
                <h1 class="text-xl font-bold text-text dark:text-text-dark">{{ __('messages.pos') }}</h1>
                <div class="flex-1 ml-4 relative">
                    <x-ui.input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="{{ __('messages.search_medicine') }} (F2)" 
                        icon="heroicon-o-magnifying-glass"
                        name="search"
                        id="search"
                        autocomplete="off"
                    />
                    @if(count($medicines) > 0)
                        <div class="absolute z-50 w-full mt-1 bg-surface dark:bg-surface-dark rounded-md shadow-lg border border-border dark:border-border-dark">
                            <ul class="max-h-60 overflow-auto">
                                @foreach($medicines as $medicine)
                                    <li>
                                        <button wire:click="addToCart({{ $medicine->id }})" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-border-dark/30 focus:outline-none focus:bg-gray-100 dark:focus:bg-border-dark/30">
                                            <div class="font-medium text-text dark:text-text-dark">{{ $medicine->name }}</div>
                                            <div class="text-sm text-text-muted dark:text-text-dark/70 flex justify-between">
                                                <span>{{ $medicine->barcode ? $medicine->barcode . ' • ' : '' }}{{ currency() }}{{ number_format($medicine->sale_price, 2) }}</span>
                                                <span class="text-xs font-semibold {{ ($medicine->inventories->first()?->quantity ?? 0) > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                                                    {{ __('messages.stock') ?? 'Stock' }}: {{ $medicine->inventories->first()?->quantity ?? 0 }}
                                                </span>
                                            </div>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            
            <div>
                <x-ui.theme-toggle />
            </div>
        </div>
        
        <!-- Quick Actions / Categories (Optional) -->
        <div class="flex-1 p-6 bg-gray-50 dark:bg-background-dark overflow-auto">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 rounded-md">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100 rounded-md relative">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->has('checkout'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100 rounded-md">
                    {{ $errors->first('checkout') }}
                </div>
            @endif

            <!-- Placeholder for quick items or empty state -->
            @if(count($cart) === 0 && empty($search))
                <div class="h-full flex flex-col items-center justify-center text-text-muted dark:text-text-dark/50">
                    <x-heroicon-o-shopping-bag class="w-16 h-16 mb-4 opacity-50 text-text-muted dark:text-text-muted-dark" />
                    <p class="text-lg text-text-muted dark:text-text-muted-dark">{{ __('messages.search_medicine') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Pane: Cart & Checkout -->
    <div class="w-96 flex flex-col bg-surface dark:bg-surface-dark">
        <!-- Customer Selection -->
        <div class="p-4 border-b border-border dark:border-border-dark">
            <div class="mb-2 font-medium flex justify-between items-center">
                <span>{{ __('messages.customers') }} <span class="text-xs text-text-muted">(F8)</span></span>
                {{ $this->createCustomerAction }}
            </div>
            <div class="relative">
                @if($customerId)
                    <div class="flex justify-between items-center w-full rounded-lg border bg-input-bg text-input-text border-input-border dark:bg-input-bg-dark dark:text-input-text-dark dark:border-input-border-dark sm:text-sm px-3 py-2">
                        <span class="truncate">{{ $selectedCustomerName }}</span>
                        <button wire:click="clearCustomer" class="text-text-muted hover:text-error dark:text-text-dark/50 dark:hover:text-error transition">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @else
                    <x-ui.input 
                        wire:model.live.debounce.300ms="customerSearch" 
                        placeholder="{{ __('messages.search_customer') ?? 'Search Name or Phone...' }}" 
                        icon="heroicon-o-magnifying-glass"
                        name="customerSearch"
                        id="customerSelect"
                        autocomplete="off"
                    />
                    @if(count($customerSearchResults) > 0)
                        <div class="absolute z-50 w-full mt-1 bg-surface dark:bg-surface-dark rounded-md shadow-lg border border-border dark:border-border-dark">
                            <ul class="max-h-60 overflow-auto">
                                @foreach($customerSearchResults as $customerResult)
                                    <li>
                                        <button wire:click="selectCustomer({{ $customerResult->id }}, '{{ addslashes($customerResult->name) }}', '{{ addslashes($customerResult->phone) }}')" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-border-dark/30 focus:outline-none focus:bg-gray-100 dark:focus:bg-border-dark/30">
                                            <div class="font-medium text-text dark:text-text-dark">{{ $customerResult->name }}</div>
                                            <div class="text-sm text-text-muted dark:text-text-dark/70">{{ $customerResult->phone }}</div>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>
        </div>
        
        <!-- Cart Items -->
        <div class="flex-1 overflow-auto p-4 space-y-4">
            @forelse($cart as $index => $item)
                <div class="flex flex-col p-3 border border-border dark:border-border-dark rounded-lg relative">
                    <div class="flex justify-between font-medium">
                        <span>{{ $item['name'] }}</span>
                        <span>{{ currency() }}{{ number_format($item['unit_price'] * $item['quantity'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-3">
                        <div class="flex items-center gap-2">
                            <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="p-1 rounded bg-gray-100 dark:bg-border-dark/30 hover:bg-gray-200 dark:hover:bg-border-dark/70 text-text dark:text-text-dark">
                                <x-heroicon-o-minus class="w-4 h-4" />
                            </button>
                            <span class="w-8 text-center">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="p-1 rounded bg-gray-100 dark:bg-border-dark/30 hover:bg-gray-200 dark:hover:bg-border-dark/70 text-text dark:text-text-dark">
                                <x-heroicon-o-plus class="w-4 h-4" />
                            </button>
                        </div>
                        <button wire:click="removeFromCart({{ $index }})" class="text-error hover:text-error-dark dark:text-error dark:hover:text-error-dark">
                            <x-heroicon-o-trash class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center text-text-muted dark:text-text-dark/50 py-8">
                    {{ __('messages.cart') }} is empty
                </div>
            @endforelse
        </div>
        
        <!-- Totals & Checkout Button -->
        <div class="p-4 bg-gray-50 dark:bg-background-dark border-t border-border dark:border-border-dark">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm text-text dark:text-text-dark">
                    <span>{{ __('messages.sub_total') }}</span>
                    <span>{{ currency() }}{{ number_format($this->subTotal, 2) }}</span>
                </div>
                
                <div class="flex justify-between items-center text-sm text-text dark:text-text-dark">
                    <span>{{ __('messages.discount') }}</span>
                    <input type="number" step="0.01" wire:model.live.debounce.500ms="discount" class="w-24 text-right px-2 py-1 rounded-lg border bg-input-bg text-input-text border-input-border dark:bg-input-bg-dark dark:text-input-text-dark dark:border-input-border-dark sm:text-sm focus:ring-primary focus:border-primary">
                </div>
                
                <div class="flex justify-between font-bold text-lg pt-2 border-t border-border dark:border-border-dark text-text dark:text-text-dark">
                    <span>{{ __('messages.total') }}</span>
                    <span>{{ currency() }}{{ number_format($this->total, 2) }}</span>
                </div>
            </div>
            
            <x-ui.button fullWidth size="lg" wire:click="$set('showCheckoutModal', true)" :disabled="count($cart) === 0">
                {{ __('messages.checkout') }} (F4)
            </x-ui.button>
        </div>
    </div>

    <!-- Checkout Modal -->
    @if($showCheckoutModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-[#121212]/80 transition-opacity" aria-hidden="true" wire:click="$set('showCheckoutModal', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative z-10 inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-surface dark:bg-surface-dark rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-border dark:border-border-dark">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-text dark:text-text-dark" id="modal-title">
                        {{ __('messages.payment_method') }}
                    </h3>
                    <div class="mt-2 space-y-4">
                        <div class="flex items-center gap-4 mt-4 text-text dark:text-text-dark">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="paymentMethod" value="cash" class="text-primary focus:ring-primary border-input-border dark:border-input-border-dark bg-input-bg dark:bg-input-bg-dark">
                                <span>{{ __('messages.cash') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="paymentMethod" value="card" class="text-primary focus:ring-primary border-input-border dark:border-input-border-dark bg-input-bg dark:bg-input-bg-dark">
                                <span>{{ __('messages.card') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="paymentMethod" value="upi" class="text-primary focus:ring-primary border-input-border dark:border-input-border-dark bg-input-bg dark:bg-input-bg-dark">
                                <span>{{ __('messages.upi') }}</span>
                            </label>
                        </div>
                        
                        <div class="mt-4">
                            <x-ui.input 
                                wire:model="paymentReference" 
                                name="paymentReference"
                                label="{{ __('messages.payment_reference') }}" 
                                placeholder="Optional" 
                            />
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 sm:flex sm:flex-row-reverse gap-3">
                    <x-ui.button wire:click="checkout" target="checkout" class="w-full sm:w-auto">
                        {{ __('messages.pay_and_complete') }}
                    </x-ui.button>
                    <button type="button" wire:click="$set('showCheckoutModal', false)" class="mt-3 inline-flex w-full justify-center rounded-lg border border-border dark:border-border-dark bg-surface dark:bg-surface-dark px-4 py-2 text-base font-medium text-text dark:text-text-dark shadow-sm hover:bg-gray-50 dark:hover:bg-border-dark/30 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <x-filament-actions::modals />

    @script
    <script>
        $wire.on('sale-completed', ({ saleId }) => {
            const url = `{{ url('/sales') }}/${saleId}/receipt`;
            const printWindow = window.open(url, '_blank', 'width=400,height=600');
            if (printWindow) {
                printWindow.focus();
            }
        });
    </script>
    @endscript
</div>
