<div class="flex flex-col h-screen bg-gray-50 dark:bg-[#0f172a] text-text dark:text-text-dark font-sans"
    x-data="posTerminal()"
    @keydown.window.prevent.f2="document.getElementById('search').focus()"
    @keydown.window.prevent.f3="$wire.set('search', '')"
    @keydown.window.prevent.f4="console.log('f4')"
    @keydown.window.prevent.f5="console.log('f5')"
    @keydown.window.prevent.f6="checkout(true)"
    @keydown.window.prevent.f7="checkout(false)"
    @keydown.window.prevent.f8="printLastInvoice()"
    @keydown.window.prevent.escape="clearCart()"
    @exact-match-found.window="addToCart($event.detail.payload)"
    @customer-selected.window="customerId = $event.detail.id; selectedCustomerName = $event.detail.name"
    @customer-cleared.window="customerId = null; selectedCustomerName = ''"
    @checkout-successful.window="clearCart()"
    @branch-changed.window="clearCart()"
>
    <!-- Top Bar -->
    <div class="flex items-center justify-between px-6 py-3 bg-white dark:bg-[#1e293b] border-b border-gray-200 dark:border-gray-800">
        <!-- Search -->
        <div class="flex items-center gap-4 flex-1">
            <a href="{{ route('dashboard') }}" wire:navigate class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                 <x-heroicon-o-home class="w-5 h-5" />
            </a>
             <div class="relative w-full max-w-xl"
                  x-data="{ 
                      highlightedIndex: 0,
                      focusNext() {
                          let count = this.$el.querySelectorAll('.search-item').length;
                          if (this.highlightedIndex < count - 1) this.highlightedIndex++;
                      },
                      focusPrev() {
                          if (this.highlightedIndex > 0) this.highlightedIndex--;
                      },
                      selectItem() {
                          let items = this.$el.querySelectorAll('.search-item');
                          if (items.length > 0 && items[this.highlightedIndex]) {
                              items[this.highlightedIndex].click();
                          } else {
                              $wire.handleEnter();
                          }
                      }
                  }"
                  @keydown.arrow-down.prevent="focusNext()"
                  @keydown.arrow-up.prevent="focusPrev()"
                  @keydown.enter.prevent="selectItem()">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                  </div>
                  <input wire:model.live.debounce.300ms="search" id="search" type="text" placeholder="{{ __('messages.search_medicine') }}" class="w-full pl-10 pr-12 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-[#0f172a] focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm">
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span class="text-xs text-gray-400 border border-gray-300 dark:border-gray-700 rounded px-1.5 py-0.5">F2</span>
                  </div>
                  
                  @if(count($medicines) > 0)
                      <div class="absolute z-50 w-full mt-1 bg-white dark:bg-surface-dark rounded-md shadow-xl border border-gray-200 dark:border-gray-800">
                          <ul class="max-h-60 overflow-auto">
                              @foreach($medicines as $medicine)
                                  @php
                                      $firstBatch = $medicine->inventories->first()?->batches->first();
                                      $payload = json_encode([
                                          'id' => $medicine->id,
                                          'name' => $medicine->name,
                                          'price' => (float)$medicine->mrp,
                                          'batch_id' => $firstBatch?->id,
                                          'batch_number' => $firstBatch?->batch_number ?? '--',
                                          'expiry' => $firstBatch?->expiry_date ? \Carbon\Carbon::parse($firstBatch->expiry_date)->format('m/y') : '--/--',
                                          'tax_rate' => (float)($medicine->tax?->rate ?? 0),
                                          'tax_name' => $medicine->tax?->name ?? '0%',
                                          'available' => $medicine->inventories->first()?->batches->sum('available_quantity') ?? 0,
                                      ]);
                                  @endphp
                                  <li>
                                      <button @click="addToCart({{ $payload }}); $wire.set('search', '')"  
                                              class="search-item w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none border-b border-gray-100 dark:border-gray-800 last:border-0 transition-colors"
                                              :class="{ 'bg-gray-100 dark:bg-gray-700': highlightedIndex === {{ $loop->index }} }">
                                          <div class="font-medium text-gray-900 dark:text-gray-100">
                                              {{ $medicine->name }}
                                              @if(count($medicines) === 1)
                                                  <span class="ml-2 text-xs text-blue-600 dark:text-blue-400 font-normal bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 px-1.5 py-0.5 rounded shadow-sm">(Enter ↵)</span>
                                              @endif
                                          </div>
                                          <div class="text-sm text-gray-500 dark:text-gray-400 flex justify-between mt-1">
                                              <span>{{ $medicine->barcode ? $medicine->barcode . ' • ' : '' }}{{ currency() }}{{ number_format($medicine->mrp, 2) }}</span>
                                              <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ ($medicine->inventories->first()?->quantity ?? 0) > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
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
        
        <!-- Top Right Actions -->
        <div class="flex items-center gap-3">
            @if(count($this->availableBranches) > 1)
                <select wire:model.live="selectedBranchId" class="text-sm border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 focus:ring-primary focus:border-primary py-2 pl-3 pr-10">
                    @foreach($this->availableBranches as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            @elseif(count($this->availableBranches) === 1)
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 border px-3 py-2 rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    {{ $this->availableBranches->first() }}
                </span>
            @endif

            <button @click="newSale()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                <x-heroicon-o-plus class="w-4 h-4" /> New Sale
            </button>
            
            <div class="h-6 w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
            
            <x-ui.theme-toggle />
            
            <div class="flex items-center gap-2 ml-2">
                <div class="text-right hidden md:block">
                    <div class="text-sm font-semibold leading-none">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cashier</div>
                </div>
                <button class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="flex flex-1 overflow-hidden p-4 gap-4">
        
        <!-- Left Panel: Cart & Actions -->
        <div class="flex flex-col flex-1 bg-white dark:bg-[#1e293b] rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            
            <!-- Cart Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-lg font-bold">{{ __('messages.cart') ?? 'Cart' }} <span class="text-gray-500 font-normal">(<span x-text="cart.length"></span> Items)</span></h2>
                <button @click="clearCart()" class="text-sm text-gray-500 hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1 transition">
                    <x-heroicon-o-trash class="w-4 h-4" /> Clear Cart
                </button>
            </div>
            
            <!-- Cart Table Area -->
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="sticky top-0 bg-white dark:bg-[#1e293b] text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800 z-10">
                        <tr>
                            <th class="px-4 py-2 font-medium">#</th>
                            <th class="px-4 py-2 font-medium">Medicine</th>
                            <th class="px-4 py-2 font-medium">Batch / Expiry</th>
                            <th class="px-4 py-2 font-medium">Unit Price</th>
                            <th class="px-4 py-2 font-medium text-center">Qty</th>
                            <th class="px-4 py-2 font-medium text-right">Price without tax</th>
                            <th class="px-4 py-2 font-medium text-right">Tax</th>
                            <th class="px-4 py-2 font-medium text-right">Total</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-2 text-gray-500" x-text="index + 1"></td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-gray-100" x-text="item.name"></div>
                                            <div class="text-xs text-gray-500" x-show="item.sku" x-text="'SKU: ' + item.sku"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="text-gray-900 dark:text-gray-300" x-text="item.batch_number"></div>
                                    <div class="text-xs text-gray-500" x-text="item.expiry_date"></div>
                                </td>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-300 font-medium">{{ currency() }}<span x-text="formatCurrency(item.unit_price)"></span></td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-center gap-3 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 w-28 mx-auto bg-white dark:bg-[#1e293b]">
                                        <button @click="updateQuantity(index, item.quantity - 1)" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                            <x-heroicon-o-minus class="w-4 h-4" />
                                        </button>
                                        <input type="number" x-model.number="item.quantity" @change="updateQuantity(index, item.quantity)" class="font-medium w-10 text-center bg-transparent border-0 p-0 focus:ring-0 appearance-none m-0 [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="1">
                                        <button @click="updateQuantity(index, item.quantity + 1)" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                            <x-heroicon-o-plus class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-gray-300">
                                    {{ currency() }}<span x-text="formatCurrency(lineSubtotal(item))"></span>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <div class="text-gray-900 dark:text-gray-300" x-text="item.tax_name"></div>
                                    <div class="text-xs text-gray-500">{{ currency() }}<span x-text="formatCurrency(lineTaxAmount(item))"></span></div>
                                </td>
                                <td class="px-4 py-2 text-right font-bold text-gray-900 dark:text-gray-100">
                                    {{ currency() }}<span x-text="formatCurrency(lineTotalAmount(item))"></span>
                                </td>
                                <td class="px-2 py-2 text-right">
                                    <button @click="removeFromCart(index)" class="text-gray-400 hover:text-red-500 transition">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="cart.length === 0" x-cloak>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                {{ __('messages.search_medicine') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Left Panel Footer (Totals & Actions) -->
            <div class="border-t border-gray-200 dark:border-gray-800 p-3 flex flex-col xl:flex-row gap-4">
                <!-- Actions Grid -->
                <div class="flex-1 flex flex-col">
                    <textarea x-model="notes" class="w-full h-full border border-gray-200 dark:border-gray-700 rounded-lg p-2 text-xs bg-gray-50 dark:bg-gray-800 focus:ring-primary focus:border-primary placeholder-gray-400 min-h-[50px]" placeholder="Add Note (optional)..."></textarea>
                </div>
                
                <!-- Totals Breakdown -->
                <div class="w-full xl:w-64 space-y-1">
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>{{ __('messages.sub_total') }} (<span x-text="cart.length"></span> items)</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ currency() }}<span x-text="formatCurrency(subTotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 items-center">
                        <span>{{ __('messages.discount') }}</span>
                        <div class="flex items-center gap-1">
                            <span class="text-red-500 font-medium">- {{ currency() }}</span>
                            <input type="number" step="0.01" x-model.number="discount" class="w-16 text-right px-1 py-0.5 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-[#1e293b] focus:outline-none focus:border-primary text-red-500 font-medium">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>CGST</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ currency() }}<span x-text="formatCurrency(taxAmount / 2)"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>SGST</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ currency() }}<span x-text="formatCurrency(taxAmount / 2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                        <label class="flex items-center cursor-pointer gap-1">
                            <input type="checkbox" x-model="applyRoundOff" class="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary h-3 w-3">
                            <span>Round Off</span>
                        </label>
                        <span class="font-medium text-gray-900 dark:text-white">
                            <span x-show="roundOffAmount < 0">-</span>{{ currency() }}<span x-text="formatCurrency(Math.abs(roundOffAmount))"></span>
                        </span>
                    </div>
                    <div class="pt-1.5 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center mt-1">
                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ __('messages.total') }}</span>
                        <span class="text-xl font-bold text-green-600 dark:text-green-500">{{ currency() }}<span x-text="formatCurrency(total)"></span></span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Right Panel: Customer & Payment -->
        <div class="w-80 flex flex-col gap-4 overflow-y-auto pr-2 pb-2">
            
            <!-- Customer Block -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                <div class="flex items-center gap-2 mb-3 font-bold text-gray-800 dark:text-gray-200">
                    <x-heroicon-o-user class="w-5 h-5" /> Customer
                </div>
                
                <div class="relative mb-3">
                    <template x-if="customerId">
                        <div class="flex justify-between items-center w-full rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold dark:bg-blue-900/30 dark:text-blue-400">
                                    <span x-text="selectedCustomerName ? selectedCustomerName.substring(0,1) : ''"></span>
                                </div>
                                <span class="font-medium text-sm truncate max-w-[180px]"><span x-text="selectedCustomerName"></span></span>
                            </div>
                            <button @click="clearCustomer()" class="text-gray-400 hover:text-red-500 transition p-1">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>
                    </template><template x-if="!customerId">
                        <div class="flex justify-between items-center w-full rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 px-4 py-3 cursor-pointer">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 flex items-center justify-center text-sm">
                                    <x-heroicon-s-user class="w-4 h-4" />
                                </div>
                                <span class="font-medium text-sm">Walk-in Customer</span>
                            </div>
                            <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" />
                        </div>
                    </template>
                </div>
                
                <template x-if="!customerId">
                    <div class="flex gap-2 relative">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="customerSearch" wire:keydown.enter.prevent="handleCustomerEnter" type="text" placeholder="{{ __('messages.search_customer') ?? 'Search Name or Phone...' }}" class="w-full pl-9 pr-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                        </div>
                        <button wire:click="mountAction('createCustomer')" class="px-3 py-2 bg-green-50 text-green-600 border border-green-200 rounded-lg hover:bg-green-100 transition dark:bg-green-900/20 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/40">
                            <x-heroicon-o-plus class="w-5 h-5" />
                        </button>
                        
                        @if(count($customerSearchResults) > 0)
                            <div class="absolute z-50 w-full mt-10 bg-white dark:bg-surface-dark rounded-md shadow-xl border border-gray-200 dark:border-gray-800">
                                <ul class="max-h-60 overflow-auto">
                                    @foreach($customerSearchResults as $customerResult)
                                        <li>
                                            <button wire:click="selectCustomer({{ $customerResult->id }}, '{{ addslashes($customerResult->name) }}', '{{ addslashes($customerResult->phone) }}')" class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none border-b border-gray-100 dark:border-gray-800 last:border-0 transition">
                                                <div class="font-medium">
                                                    {{ $customerResult->name }}
                                                    @if(count($customerSearchResults) === 1)
                                                        <span class="ml-2 text-xs text-blue-600 dark:text-blue-400 font-normal bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 px-1.5 py-0.5 rounded shadow-sm">(Enter ↵)</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $customerResult->phone }}</div>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </template>
            </div>
            
            <!-- Payment Block -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4 flex-1 flex flex-col">
                <div class="flex items-center gap-2 mb-4 font-bold text-gray-800 dark:text-gray-200">
                    <x-heroicon-o-banknotes class="w-5 h-5" /> Payment
                </div>
                
                <div class="flex justify-between items-center mb-6">
                    <span class="text-gray-600 dark:text-gray-400">Total Amount</span>
                    <span class="text-3xl font-bold text-green-600">{{ currency() }}<span x-text="formatCurrency(total)"></span></span>
                </div>
                
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600 dark:text-gray-400">Received</span>
                    <div class="relative w-32">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium">{{ currency() }}</span>
                        </div>
                        <input type="number" x-model.number="receivedAmount" class="w-full pl-8 pr-3 py-2 text-right border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-primary focus:border-primary font-medium" placeholder="0.00">
                    </div>
                </div>
                
                <div class="grid grid-cols-4 gap-2 mb-6">
                    <button @click="receivedAmount = 200" class="py-2 border font-medium rounded-lg transition" :class="receivedAmount == 200 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'" font-medium rounded-lg transition">
                        {{ currency() }}200
                    </button>
                    <button @click="receivedAmount = 500" class="py-2 border font-medium rounded-lg transition" :class="receivedAmount == 500 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'" font-medium rounded-lg transition">
                        {{ currency() }}500
                    </button>
                    <button @click="receivedAmount = 1000" class="py-2 border font-medium rounded-lg transition" :class="receivedAmount == 1000 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'" font-medium rounded-lg transition">
                        {{ currency() }}1000
                    </button>
                    <button @click="receivedAmount = total" class="py-2 border font-medium text-sm rounded-lg transition" :class="receivedAmount == total && total > 0 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'" font-medium text-sm rounded-lg transition">
                        Exact
                    </button>
                </div>
                
                <div class="mb-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.payment_method') }}</div>
                    <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-800 p-1 gap-1">
                        <button @click="paymentMethod = 'cash'" class="flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition" :class="paymentMethod === 'cash' ? 'bg-white dark:bg-gray-600 shadow text-green-600 dark:text-green-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent'">
                            <x-heroicon-o-banknotes class="w-4 h-4" /> Cash
                        </button>
                        <button @click="paymentMethod = 'upi'" class="flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition" :class="paymentMethod === 'upi' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent'">
                            <x-heroicon-o-qr-code class="w-4 h-4" /> UPI
                        </button>
                        <button @click="paymentMethod = 'card'" class="flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition" :class="paymentMethod === 'card' ? 'bg-white dark:bg-gray-600 shadow text-purple-600 dark:text-purple-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent'">
                            <x-heroicon-o-credit-card class="w-4 h-4" /> Card
                        </button>
                    </div>
                </div>

                <template x-if="['upi', 'card'].includes(paymentMethod)">
                    <div class="mb-6">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.transaction_number') ?? 'Transaction Number (Optional)' }}</div>
                        <input type="text" x-model="paymentReference" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-[#1e293b] focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="e.g. TXN123456789">
                    </div>
                </template>
                
                <div class="flex justify-between items-center mb-8 border-t border-gray-200 dark:border-gray-800 pt-4">
                    <span class="text-gray-600 dark:text-gray-400">Change</span>
                    <span class="text-2xl font-bold text-green-600">{{ currency() }}<span x-text="formatCurrency(changeAmount)"></span></span>
                </div>
                
                @if($errors->has('checkout'))
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-sm border border-red-200 dark:border-red-800">
                        {{ $errors->first('checkout') }}
                    </div>
                @endif
                
                <div class="mt-auto space-y-3">
                    <button @click="checkout(true)" class="w-full py-4 bg-[#219653] hover:bg-green-700 text-white font-bold rounded-xl flex justify-between items-center px-6 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" :disabled="cart.length === 0">
                        <span>Pay & Print Invoice</span>
                        <span class="text-green-200 text-sm font-normal">F6</span>
                    </button>
                    <button @click="checkout(false)" class="w-full py-4 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold rounded-xl flex justify-between items-center px-6 transition disabled:opacity-50 disabled:cursor-not-allowed" :disabled="cart.length === 0">
                        <span>Pay (No Print)</span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm font-normal">F7</span>
                    </button>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Bottom Footer Bar -->
    <div class="bg-gray-50 dark:bg-[#0f172a] border-t border-gray-200 dark:border-gray-800 px-6 py-4 flex gap-4 overflow-x-auto">
        <button @click="checkout(true)" class="flex items-center gap-2 bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed" :disabled="cart.length === 0">
            <span class="bg-gray-100 dark:bg-gray-800 text-gray-500 px-2 py-0.5 rounded text-xs border border-gray-200 dark:border-gray-700 shadow-sm">F6</span> Pay & Print
        </button>
        <button @click="checkout(false)" class="flex items-center gap-2 bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed" :disabled="cart.length === 0">
            <span class="bg-gray-100 dark:bg-gray-800 text-gray-500 px-2 py-0.5 rounded text-xs border border-gray-200 dark:border-gray-700 shadow-sm">F7</span> Pay (No Print)
        </button>
        <button @click="printLastInvoice()" class="flex items-center gap-2 bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            <span class="bg-gray-100 dark:bg-gray-800 text-gray-500 px-2 py-0.5 rounded text-xs border border-gray-200 dark:border-gray-700 shadow-sm">F8</span> Print Invoice
        </button>
        <div class="border-r border-gray-300 dark:border-gray-700 mx-1"></div>
        <button @click="holdInvoice()" class="flex items-center gap-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-lg px-4 py-2 font-medium text-sm text-yellow-700 dark:text-yellow-500 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition disabled:opacity-50 disabled:cursor-not-allowed" :disabled="cart.length === 0">
            Hold Invoice
        </button>
        <button wire:click="mountAction('viewDrafts')" class="flex items-center gap-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700/50 rounded-lg px-4 py-2 font-medium text-sm text-blue-700 dark:text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
            Drafts
        </button>
        <button @click="clearCart()" class="flex items-center gap-2 ml-auto bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 font-medium text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            <span class="bg-gray-100 dark:bg-gray-800 text-gray-500 px-2 py-0.5 rounded text-xs border border-gray-200 dark:border-gray-700 shadow-sm">Esc</span> Cancel Sale
        </button>
    </div>

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
              $wire.on('print-estimate', ({ html }) => {
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            if (printWindow) {
                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.focus();
            }
        });

        Alpine.data('posTerminal', () => ({
            cart: [],
            discount: 0,
            
            init() {
                window.addEventListener('draft-loaded', (e) => {
                    console.log('draft-loaded event received:', e.detail);
                    
                    // In Livewire 3 with named arguments, it's typically e.detail.payload
                    // If it's an array (no named args), it might be e.detail[0].payload
                    const payload = e.detail.payload || (e.detail[0] && e.detail[0].payload) || e.detail;
                    const customerName = e.detail.customerName || (e.detail[0] && e.detail[0].customerName) || '';
                    
                    if (payload) {
                        this.cart = payload.cart || [];
                        this.discount = payload.discount || 0;
                        this.notes = payload.notes || '';
                        this.applyRoundOff = payload.applyRoundOff !== undefined ? payload.applyRoundOff : true;
                        this.paymentMethod = payload.paymentMethod || 'cash';
                        this.paymentReference = payload.paymentReference || '';
                        this.customerId = payload.customerId || null;
                        this.selectedCustomerName = customerName || '';
                    }
                });
                
                window.addEventListener('draft-saved', () => {
                    this.clearCart();
                });
            },
            
            receivedAmount: null,
            notes: '',
            applyRoundOff: true,
            paymentMethod: 'cash',
            paymentReference: '',
            customerId: null,
            selectedCustomerName: '',

            get subTotal() {
                let total = 0;
                for (let item of this.cart) {
                    let itemTotal = item.price * item.quantity;
                    if (item.is_tax_inclusive) {
                        let taxRate = parseFloat(item.tax_rate) || 0;
                        let itemTax = taxRate > 0 ? itemTotal - (itemTotal / (1 + (taxRate / 100))) : 0;
                        total += (itemTotal - itemTax);
                    } else {
                        total += itemTotal;
                    }
                }
                return total;
            },
            get taxAmount() {
                return this.cart.reduce((sum, item) => {
                    let itemTotal = item.price * item.quantity;
                    let taxRate = parseFloat(item.tax_rate) || 0;
                    if (taxRate <= 0) return sum;
                    
                    if (item.is_tax_inclusive) {
                        return sum + (itemTotal - (itemTotal / (1 + (taxRate / 100))));
                    }
                    return sum + (itemTotal * (taxRate / 100));
                }, 0);
            },
            get roundOffAmount() {
                if (!this.applyRoundOff) return 0;
                let rawTotal = (this.subTotal + this.taxAmount) - parseFloat(this.discount || 0);
                return Math.round(rawTotal) - rawTotal;
            },
            get total() {
                let rawTotal = (this.subTotal + this.taxAmount) - parseFloat(this.discount || 0);
                if (this.applyRoundOff) {
                    return Math.max(0, Math.round(rawTotal));
                }
                return Math.max(0, rawTotal);
            },
            get changeAmount() {
                if (this.receivedAmount === null || this.receivedAmount === '') return 0;
                return Math.max(0, parseFloat(this.receivedAmount) - this.total);
            },
            
            lineTaxAmount(item) {
                let itemTotal = item.price * item.quantity;
                let taxRate = parseFloat(item.tax_rate) || 0;
                if (taxRate <= 0) return 0;
                
                if (item.is_tax_inclusive) {
                    return itemTotal - (itemTotal / (1 + (taxRate / 100)));
                }
                return itemTotal * (taxRate / 100);
            },
            lineSubtotal(item) {
                let itemTotal = item.price * item.quantity;
                if (item.is_tax_inclusive) {
                    return itemTotal - this.lineTaxAmount(item);
                }
                return itemTotal;
            },
            lineTotalAmount(item) {
                let itemTotal = item.price * item.quantity;
                if (item.is_tax_inclusive) {
                    return itemTotal;
                }
                return itemTotal + this.lineTaxAmount(item);
            },
            
            addToCart(payload) {
                if (!payload || !payload.id) return;
                let index = this.cart.findIndex(i => i.id === payload.id);
                let currentQty = index !== -1 ? this.cart[index].quantity : 0;
                
                if (currentQty + 1 > payload.available) {
                    $wire.dispatch('notify', { title: 'Not enough stock available', type: 'danger' });
                    return;
                }
                
                if (index !== -1) {
                    this.cart[index].quantity++;
                } else {
                    this.cart.push({
                        medicine_id: payload.id, // mapped for checkout
                        id: payload.id,
                        name: payload.name,
                        sku: payload.sku,
                        unit_price: payload.price,
                        price: payload.price,
                        quantity: 1,
                        inventory_batch_id: payload.batch_id,
                        batch_number: payload.batch_number,
                        expiry_date: payload.expiry,
                        tax_rate: payload.tax_rate,
                        tax_name: payload.tax_name,
                        is_tax_inclusive: payload.is_tax_inclusive,
                        available_stock: payload.available
                    });
                }
            },
            
            updateQuantity(index, quantity) {
                quantity = parseInt(quantity);
                if (isNaN(quantity) || quantity < 1) {
                    this.cart.splice(index, 1);
                    return;
                }
                
                let item = this.cart[index];
                if (quantity > item.available_stock) {
                    $wire.dispatch('notify', { title: 'Not enough stock available', type: 'danger' });
                    item.quantity = Math.max(1, item.available_stock);
                    return;
                }
                
                item.quantity = quantity;
            },
            
            removeFromCart(index) {
                this.cart.splice(index, 1);
            },
            
            clearCart() {
                this.cart = [];
                this.discount = 0;
                this.receivedAmount = null;
                this.notes = '';
                this.customerId = null;
                this.selectedCustomerName = '';
                $wire.clearCustomer();
            },
            
            checkout(print) {
                if (this.cart.length === 0) return;
                
                $wire.processCheckout({
                    cart: JSON.parse(JSON.stringify(this.cart)),
                    discount: this.discount,
                    paymentMethod: this.paymentMethod,
                    paymentReference: this.paymentReference,
                    applyRoundOff: this.applyRoundOff,
                    notes: this.notes,
                    customerId: this.customerId
                }, print);
            },
            
            newSale() {
                if (this.cart.length > 0) {
                    if (confirm("You have items in the cart. Do you want to save them as a draft?")) {
                        this.holdInvoice();
                    }
                } else {
                    this.clearCart();
                }
            },
            
            holdInvoice() {
                if (this.cart.length === 0) return;
                
                let refName = prompt("Enter a reference name for this draft (optional):", this.selectedCustomerName || "");
                if (refName === null) {
                    // Cancelled prompt
                    return;
                }
                
                $wire.holdInvoice({
                    cart: JSON.parse(JSON.stringify(this.cart)),
                    discount: this.discount,
                    paymentMethod: this.paymentMethod,
                    paymentReference: this.paymentReference,
                    applyRoundOff: this.applyRoundOff,
                    notes: this.notes,
                    customerId: this.customerId,
                    referenceName: refName
                });
            },
            
            printLastInvoice() {
                $wire.printLastInvoice({
                    cart: JSON.parse(JSON.stringify(this.cart)),
                    discount: this.discount,
                    applyRoundOff: this.applyRoundOff
                });
            },
            
            formatCurrency(amount) {
                return Number(amount).toFixed(2);
            }
        }));
    </script>
    @endscript
</div>
