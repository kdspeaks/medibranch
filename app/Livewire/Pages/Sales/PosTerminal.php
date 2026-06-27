<?php

namespace App\Livewire\Pages\Sales;

use App\Models\Medicine;
use App\Models\Customer;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\Attributes\Layout;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;

#[Layout('layouts.app')]
class PosTerminal extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    public $cart = [];
    public $search = '';
    public $medicines = [];
    
    public $customerId = null;
    public $discount = 0;
    public $paymentMethod = 'cash';
    public $paymentReference = '';
    
    public $showCheckoutModal = false;

    public $customerSearch = '';
    public $customerSearchResults = [];
    public $selectedCustomerName = '';
    
    public function updatedSearch()
    {
        if (strlen($this->search) > 1) {
            $branchId = activeBranch()->id ?? null;

            // Check exact barcode match first
            $exactMatch = Medicine::where('barcode', $this->search)->first();
            if ($exactMatch) {
                $this->addToCart($exactMatch->id);
                return;
            }

            // Otherwise, load matches with inventory for the current branch
            $this->medicines = Medicine::where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
                })
                ->with(['inventories' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                }])
                ->take(10)
                ->get();
        } else {
            $this->medicines = [];
        }
    }
    
    public function addToCart($medicineId)
    {
        $medicine = Medicine::find($medicineId);
        if (!$medicine) return;
        
        $branchId = activeBranch()->id ?? null;
        if (!$branchId) {
            Notification::make()
                ->title(__('messages.select_branch_first'))
                ->danger()
                ->send();
            return;
        }

        $availableStock = \App\Models\Inventory::forBranch($branchId)
            ->where('medicine_id', $medicineId)
            ->first()?->quantity ?? 0;
            
        $index = collect($this->cart)->search(fn($item) => $item['medicine_id'] === $medicineId);
        $currentCartQty = $index !== false ? $this->cart[$index]['quantity'] : 0;
        
        if ($currentCartQty + 1 > $availableStock) {
            Notification::make()
                ->title(__('messages.not_enough_stock', ['available' => $availableStock]))
                ->danger()
                ->send();
            return;
        }

        if ($index !== false) {
            $this->cart[$index]['quantity']++;
        } else {
            $this->cart[] = [
                'medicine_id' => $medicine->id,
                'name' => $medicine->name,
                'unit_price' => $medicine->sale_price,
                'quantity' => 1,
            ];
        }
        
        $this->search = '';
        $this->medicines = [];
    }
    
    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
            return;
        } 
        
        $medicineId = $this->cart[$index]['medicine_id'];
        $branchId = activeBranch()->id ?? null;
        
        $availableStock = \App\Models\Inventory::forBranch($branchId)
            ->where('medicine_id', $medicineId)
            ->first()?->quantity ?? 0;
            
        if ($quantity > $availableStock) {
            Notification::make()
                ->title(__('messages.not_enough_stock', ['available' => $availableStock]))
                ->danger()
                ->send();
            return;
        }
        
        $this->cart[$index]['quantity'] = $quantity;
    }
    
    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }
    
    public function getSubTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['unit_price'] * $item['quantity']);
    }
    
    public function getTotalProperty()
    {
        return max(0, $this->subTotal - $this->discount);
    }
    
    public function checkout(SaleService $saleService)
    {
        $this->validate([
            'cart' => 'required|array|min:1',
            'discount' => 'numeric|min:0',
        ]);
        
        $branchId = activeBranch()->id ?? null;
        if (!$branchId) {
            $this->addError('cart', 'No active branch selected.');
            return;
        }
        
        try {
            $sale = $saleService->checkout(
                branchId: $branchId,
                userId: auth()->id(),
                customerId: empty($this->customerId) ? null : $this->customerId,
                cartItems: $this->cart,
                discount: (float)$this->discount,
                paymentMethod: $this->paymentMethod,
                paymentStatus: 'paid',
                paymentReference: $this->paymentReference,
                notes: null
            );
            
            $this->reset(['cart', 'discount', 'customerId', 'paymentMethod', 'paymentReference', 'showCheckoutModal']);
            $this->dispatch('sale-completed', saleId: $sale->id);
            
            Notification::make()
                ->title(__('messages.sale_completed'))
                ->success()
                ->send();
            
        } catch (\Exception $e) {
            $this->addError('checkout', $e->getMessage());
        }
    }

    public function createCustomerAction(): Action
    {
        return Action::make('createCustomer')
            ->label(__('messages.create_customer') ?? 'New')
            ->icon('heroicon-o-plus')
            ->modalHeading(__('messages.create_customer'))
            ->form([
                TextInput::make('name')->label(__('messages.customer_name'))->required()->maxLength(255),
                TextInput::make('phone')->label(__('messages.phone_number'))->required()->maxLength(20)->unique(Customer::class, ignoreRecord: true),
                TextInput::make('email')->label(__('messages.email'))->email()->maxLength(255),
                Textarea::make('address')->label(__('messages.address')),
            ])
            ->action(function (array $data) {
                $customer = Customer::create($data);
                
                $this->customerId = $customer->id;
                $this->selectedCustomerName = $customer->name . ' (' . $customer->phone . ')';
                
                Notification::make()
                    ->title(__('messages.customer_created') ?? 'Customer created successfully.')
                    ->success()
                    ->send();
            });
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) > 1) {
            $this->customerSearchResults = Customer::where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                ->take(10)
                ->get();
        } else {
            $this->customerSearchResults = [];
        }
    }

    public function selectCustomer($id, $name, $phone)
    {
        $this->customerId = $id;
        $this->selectedCustomerName = $name . ' (' . $phone . ')';
        $this->customerSearch = '';
        $this->customerSearchResults = [];
    }

    public function clearCustomer()
    {
        $this->customerId = null;
        $this->selectedCustomerName = '';
    }

    public function render()
    {
        return view('livewire.sales.pos-terminal');
    }
}
