import re

with open('resources/views/livewire/sales/pos-terminal.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update x-data wrapper
content = re.sub(
    r'<div class="flex flex-col h-screen bg-gray-50 dark:bg-\[#0f172a\] text-text dark:text-text-dark font-sans"\s*x-data\s*@keydown\.window\.prevent\.f2',
    """<div class="flex flex-col h-screen bg-gray-50 dark:bg-[#0f172a] text-text dark:text-text-dark font-sans"
    x-data="posTerminal()"
    @keydown.window.prevent.f2""",
    content
)

content = content.replace(
    """    @keydown.window.prevent.f6="$wire.checkout(true)"
    @keydown.window.prevent.f7="$wire.checkout(false)"
    @keydown.window.prevent.f8="$wire.printLastInvoice()"
    @keydown.window.prevent.escape="$wire.clearCart()"
>""",
    """    @keydown.window.prevent.f6="checkout(true)"
    @keydown.window.prevent.f7="checkout(false)"
    @keydown.window.prevent.f8="printLastInvoice()"
    @keydown.window.prevent.escape="clearCart()"
    @exact-match-found.window="addToCart($event.detail.payload)"
    @customer-selected.window="customerId = $event.detail.id; selectedCustomerName = $event.detail.name"
    @customer-cleared.window="customerId = null; selectedCustomerName = ''"
    @checkout-successful.window="clearCart()"
>"""
)

# 2. Update search results addToCart
search_replacement = """@php
                                      $firstBatch = $medicine->inventories->first()?->batches->first();
                                      $payload = json_encode([
                                          'id' => $medicine->id,
                                          'name' => $medicine->name,
                                          'price' => (float)$medicine->sale_price,
                                          'batch_id' => $firstBatch?->id,
                                          'batch_number' => $firstBatch?->batch_number ?? '--',
                                          'expiry' => $firstBatch?->expiry_date ? \\Carbon\\Carbon::parse($firstBatch->expiry_date)->format('m/y') : '--/--',
                                          'tax_rate' => (float)($medicine->tax?->rate ?? 0),
                                          'tax_name' => $medicine->tax?->name ?? '0%',
                                          'available' => $medicine->inventories->first()?->batches->sum('available_quantity') ?? 0,
                                      ]);
                                  @endphp
                                  <li>
                                      <button @click="addToCart({{ $payload }}); $wire.set('search', '')" """
content = re.sub(r'<li>\s*<button wire:click="addToCart\(\{\{ \$medicine->id \}\}\)"', search_replacement.replace('\\', '\\\\'), content)

# 3. Update Cart Loop
cart_loop_original = """<tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($cart as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-2 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="font-bold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="text-gray-900 dark:text-gray-300">{{ $item['batch_number'] ?? '--' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item['expiry_date'] ?? '--/--' }}</div>
                                </td>
                                <td class="px-4 py-2 text-gray-500">{{ currency() }}{{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-4 py-2 font-medium">{{ currency() }}{{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-center gap-3 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 w-28 mx-auto bg-white dark:bg-[#1e293b]">
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                            <x-heroicon-o-minus class="w-4 h-4" />
                                        </button>
                                        <input wire:key="qty-{{ $index }}-{{ $item['medicine_id'] }}" 
                                               @quantity-corrected.window="if ($event.detail.index == {{ $index }}) $el.value = $event.detail.quantity"
                                               type="number" wire:model.live.debounce.500ms="cart.{{ $index }}.quantity" class="font-medium w-10 text-center bg-transparent border-0 p-0 focus:ring-0 appearance-none m-0 [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="1">
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                            <x-heroicon-o-plus class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                    {{ $item['tax_name'] ?? '0%' }}
                                </td>
                                <td class="px-4 py-2 text-right font-bold text-gray-900 dark:text-gray-100">
                                    {{ currency() }}{{ number_format($item['unit_price'] * $item['quantity'], 2) }}
                                </td>
                                <td class="px-2 py-2 text-right">
                                    <button wire:click="removeFromCart({{ $index }})" class="text-gray-400 hover:text-red-500 transition">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                    {{ __('messages.search_medicine') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>"""

cart_loop_new = """<tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-2 text-gray-500" x-text="index + 1"></td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="font-bold text-gray-900 dark:text-gray-100" x-text="item.name"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="text-gray-900 dark:text-gray-300" x-text="item.batch_number"></div>
                                    <div class="text-xs text-gray-500" x-text="item.expiry_date"></div>
                                </td>
                                <td class="px-4 py-2 text-gray-500">{{ currency() }}<span x-text="formatCurrency(item.unit_price)"></span></td>
                                <td class="px-4 py-2 font-medium">{{ currency() }}<span x-text="formatCurrency(item.unit_price)"></span></td>
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
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300" x-text="item.tax_name"></td>
                                <td class="px-4 py-2 text-right font-bold text-gray-900 dark:text-gray-100">
                                    {{ currency() }}<span x-text="formatCurrency(item.unit_price * item.quantity)"></span>
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
                    </tbody>"""

content = content.replace(cart_loop_original, cart_loop_new)

# 4. Update the cart count and clear cart
content = content.replace("({{ count($cart) }} Items)", "(<span x-text=\"cart.length\"></span> Items)")
content = content.replace("wire:click=\"clearCart\"", "@click=\"clearCart()\"")

# 5. Left Panel Footer
content = content.replace("wire:model.live.debounce.500ms=\"notes\"", "x-model=\"notes\"")
content = content.replace("({{ count($cart) }} items)", "(<span x-text=\"cart.length\"></span> items)")
content = content.replace("{{ currency() }}{{ number_format($this->subTotal, 2) }}", "{{ currency() }}<span x-text=\"formatCurrency(subTotal)\"></span>")
content = content.replace("wire:model.live.debounce.500ms=\"discount\"", "x-model.number=\"discount\"")
content = content.replace("{{ currency() }}{{ number_format($this->taxAmount, 2) }}", "{{ currency() }}<span x-text=\"formatCurrency(taxAmount)\"></span>")
content = content.replace("wire:model.live=\"applyRoundOff\"", "x-model=\"applyRoundOff\"")
content = content.replace("""@if($this->roundOffAmount < 0) - @endif
                            {{ currency() }}{{ number_format(abs($this->roundOffAmount), 2) }}""", 
                            """<span x-show="roundOffAmount < 0">-</span>{{ currency() }}<span x-text="formatCurrency(Math.abs(roundOffAmount))"></span>""")
content = content.replace("{{ currency() }}{{ number_format($this->total, 2) }}", "{{ currency() }}<span x-text=\"formatCurrency(total)\"></span>")

# 6. Customer section
content = content.replace("@if($customerId)", "<template x-if=\"customerId\">")
content = content.replace("@else", "</template><template x-if=\"!customerId\">")
content = content.replace("@endif", "</template>")
content = content.replace("{{ substr($selectedCustomerName, 0, 1) }}", "<span x-text=\"selectedCustomerName ? selectedCustomerName.substring(0,1) : ''\"></span>")
content = content.replace("{{ $selectedCustomerName }}", "<span x-text=\"selectedCustomerName\"></span>")
content = content.replace("@if(!$customerId)", "<template x-if=\"!customerId\">")
content = content.replace("wire:click=\"clearCustomer\"", "@click=\"clearCustomer()\"")

# 7. Payment block
content = content.replace("wire:model.live.debounce.300ms=\"receivedAmount\"", "x-model.number=\"receivedAmount\"")
content = content.replace("wire:click=\"setReceivedAmount(200)\" class=\"py-2 border {{ $receivedAmount == 200 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}", 
                          "@click=\"receivedAmount = 200\" class=\"py-2 border font-medium rounded-lg transition\" :class=\"receivedAmount == 200 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'\"")
content = content.replace("wire:click=\"setReceivedAmount(500)\" class=\"py-2 border {{ $receivedAmount == 500 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}", 
                          "@click=\"receivedAmount = 500\" class=\"py-2 border font-medium rounded-lg transition\" :class=\"receivedAmount == 500 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'\"")
content = content.replace("wire:click=\"setReceivedAmount(1000)\" class=\"py-2 border {{ $receivedAmount == 1000 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}", 
                          "@click=\"receivedAmount = 1000\" class=\"py-2 border font-medium rounded-lg transition\" :class=\"receivedAmount == 1000 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'\"")
content = content.replace("wire:click=\"setReceivedAmount({{ $this->total }})\" class=\"py-2 border {{ $receivedAmount == $this->total && $this->total > 0 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}", 
                          "@click=\"receivedAmount = total\" class=\"py-2 border font-medium text-sm rounded-lg transition\" :class=\"receivedAmount == total && total > 0 ? 'border-green-300 bg-green-600 text-white' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'\"")

content = content.replace("wire:click=\"$set('paymentMethod', 'cash')\" class=\"flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition {{ $paymentMethod === 'cash' ? 'bg-white dark:bg-gray-600 shadow text-green-600 dark:text-green-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent' }}\"", 
                          "@click=\"paymentMethod = 'cash'\" class=\"flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition\" :class=\"paymentMethod === 'cash' ? 'bg-white dark:bg-gray-600 shadow text-green-600 dark:text-green-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent'\"")
content = content.replace("wire:click=\"$set('paymentMethod', 'upi')\" class=\"flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition {{ $paymentMethod === 'upi' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent' }}\"", 
                          "@click=\"paymentMethod = 'upi'\" class=\"flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition\" :class=\"paymentMethod === 'upi' ? 'bg-white dark:bg-gray-600 shadow text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent'\"")
content = content.replace("wire:click=\"$set('paymentMethod', 'card')\" class=\"flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition {{ $paymentMethod === 'card' ? 'bg-white dark:bg-gray-600 shadow text-purple-600 dark:text-purple-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent' }}\"", 
                          "@click=\"paymentMethod = 'card'\" class=\"flex-1 py-2 flex items-center justify-center gap-2 rounded-md font-medium text-sm transition\" :class=\"paymentMethod === 'card' ? 'bg-white dark:bg-gray-600 shadow text-purple-600 dark:text-purple-400 border border-gray-200 dark:border-gray-500' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 border border-transparent'\"")

content = content.replace("@if(in_array($paymentMethod, ['upi', 'card']))", "<template x-if=\"['upi', 'card'].includes(paymentMethod)\">")
content = content.replace("wire:model.live.debounce.500ms=\"paymentReference\"", "x-model=\"paymentReference\"")

content = content.replace("{{ currency() }}{{ number_format($this->changeAmount, 2) }}", "{{ currency() }}<span x-text=\"formatCurrency(changeAmount)\"></span>")
content = content.replace("wire:click=\"checkout(true)\"", "@click=\"checkout(true)\"")
content = content.replace("wire:click=\"checkout(false)\"", "@click=\"checkout(false)\"")
content = content.replace("{{ count($cart) === 0 ? 'disabled' : '' }}", ":disabled=\"cart.length === 0\"")
content = content.replace("wire:click=\"printLastInvoice\"", "@click=\"printLastInvoice()\"")


# Add script at the end
script_to_add = """
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posTerminal', () => ({
                cart: [],
                discount: 0,
                receivedAmount: null,
                notes: '',
                applyRoundOff: true,
                paymentMethod: 'cash',
                paymentReference: '',
                customerId: null,
                selectedCustomerName: '',

                get subTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                get taxAmount() {
                    return this.cart.reduce((sum, item) => {
                        let itemTotal = item.price * item.quantity;
                        return sum + (itemTotal * (item.tax_rate / 100));
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
                            unit_price: payload.price,
                            price: payload.price,
                            quantity: 1,
                            inventory_batch_id: payload.batch_id,
                            batch_number: payload.batch_number,
                            expiry_date: payload.expiry,
                            tax_rate: payload.tax_rate,
                            tax_name: payload.tax_name,
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
                        cart: this.cart,
                        discount: this.discount,
                        paymentMethod: this.paymentMethod,
                        paymentReference: this.paymentReference,
                        applyRoundOff: this.applyRoundOff,
                        notes: this.notes,
                        customerId: this.customerId
                    }, print);
                },
                
                printLastInvoice() {
                    $wire.printLastInvoice({
                        cart: this.cart,
                        discount: this.discount,
                        applyRoundOff: this.applyRoundOff
                    });
                },
                
                formatCurrency(amount) {
                    return Number(amount).toFixed(2);
                }
            }));
        });
    </script>
"""
content = content.replace("</script>", "</script>" + script_to_add)

with open('c:/laragon/www/medibranch/resources/views/livewire/sales/pos-terminal.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
