<?php

namespace App\Livewire\Pages\Sales;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\PosDraft;
use App\Services\PricingService;
use App\Services\SaleService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('POS Terminal')]
class PosTerminal extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[Title('POS Terminal')]
    public $cart = [];

    public $search = '';

    public $medicines = [];

    public $customerId = null;

    public $discount = 0;

    public $paymentMethod = 'cash';

    public $paymentReference = '';

    public $receivedAmount = null;

    public $notes = '';

    public $applyRoundOff = true;

    public $lastSaleId = null;

    public $customerSearch = '';

    public $customerSearchResults = [];

    public $selectedCustomerName = '';

    public $selectedBranchId = null;

    public function mount()
    {
        $this->selectedBranchId = activeBranch()?->id;

        if (! $this->selectedBranchId && $this->isSuperAdmin()) {
            $this->selectedBranchId = \App\Models\Branch::first()?->id;
        } elseif (! $this->selectedBranchId) {
            $this->selectedBranchId = auth()->user()?->branches()->where('is_active', true)->first()?->id;
        }
    }

    private function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    #[\Livewire\Attributes\Computed]
    public function availableBranches()
    {
        if ($this->isSuperAdmin()) {
            return \App\Models\Branch::pluck('name', 'id');
        }

        return auth()->user()?->branches()->where('is_active', true)->pluck('branches.name', 'branches.id') ?? collect();
    }

    public function updatedSelectedBranchId()
    {
        $this->dispatch('branch-changed');
    }

    #[\Livewire\Attributes\On('notify')]
    public function notify($title = 'Notification', $type = 'info')
    {
        Notification::make()
            ->title($title)
            ->{$type}()
            ->send();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 1) {
            $branchId = $this->selectedBranchId;

            // Check exact barcode match first
            $exactMatch = Medicine::where('barcode', $this->search)
                ->orWhere('sku', $this->search)
                ->first();

            if ($exactMatch) {
                $details = $this->getMedicineDetails($exactMatch->id);
                $firstBatch = $details->inventories->first()?->batches->first();
                $allBatches = $details->inventories->first()?->batches->map(function ($b) {
                    return [
                        'id' => $b->id,
                        'batch_number' => $b->batch_number,
                        'expiry' => $b->expiry_date ? \Carbon\Carbon::parse($b->expiry_date)->format('m/y') : '--/--',
                        'available_quantity' => $b->available_quantity,
                    ];
                })->toArray() ?? [];

                $this->dispatch('exact-match-found', payload: [
                    'id' => $details->id,
                    'name' => $details->name,
                    'sku' => $details->sku,
                    'price' => (float) $details->mrp,
                    'batch_id' => $firstBatch?->id,
                    'batch_number' => $firstBatch?->batch_number ?? '--',
                    'expiry' => $firstBatch?->expiry_date ? \Carbon\Carbon::parse($firstBatch->expiry_date)->format('m/y') : '--/--',
                    'tax_rate' => (float) ($details->tax?->rate ?? 0),
                    'tax_name' => $details->tax?->name ?? '0%',
                    'is_tax_inclusive' => (bool) $details->is_tax_inclusive,
                    'available' => $details->inventories->first()?->batches->sum('available_quantity') ?? 0,
                    'batches' => $allBatches,
                ]);
                $this->search = '';

                return;
            }

            // Otherwise, load matches with inventory for the current branch
            $this->medicines = Medicine::where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('barcode', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%');
                })
                ->when($branchId, function ($query) use ($branchId) {
                    $query->with(['inventories' => function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->with(['batches' => function ($query) {
                            $query->where('available_quantity', '>', 0)
                                ->orderBy('expiry_date', 'asc')
                                ->orderBy('created_at', 'asc');
                        }]);
                    }, 'tax']);
                })
                ->take(10)
                ->get();
        } else {
            $this->medicines = [];
        }
    }

    public function handleEnter()
    {
        if (count($this->medicines) === 1) {
            $details = $this->medicines->first();
            $firstBatch = $details->inventories->first()?->batches->first();
            $allBatches = $details->inventories->first()?->batches->map(function ($b) {
                return [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'expiry' => $b->expiry_date ? \Carbon\Carbon::parse($b->expiry_date)->format('m/y') : '--/--',
                    'available_quantity' => $b->available_quantity,
                ];
            })->toArray() ?? [];

            $this->dispatch('exact-match-found', payload: [
                'id' => $details->id,
                'name' => $details->name,
                'sku' => $details->sku,
                'price' => (float) $details->mrp,
                'batch_id' => $firstBatch?->id,
                'batch_number' => $firstBatch?->batch_number ?? '--',
                'expiry' => $firstBatch?->expiry_date ? \Carbon\Carbon::parse($firstBatch->expiry_date)->format('m/y') : '--/--',
                'tax_rate' => (float) ($details->tax?->rate ?? 0),
                'tax_name' => $details->tax?->name ?? '0%',
                'is_tax_inclusive' => (bool) $details->is_tax_inclusive,
                'available' => $details->inventories->first()?->batches->sum('available_quantity') ?? 0,
                'batches' => $allBatches,
            ]);
            $this->search = '';
        }
    }

    public function getMedicineDetails($medicineId)
    {
        $branchId = $this->selectedBranchId;

        return Medicine::with(['inventories' => function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)->with(['batches' => function ($query) {
                $query->where('available_quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->orderBy('created_at', 'asc');
            }]);
        }, 'tax'])->find($medicineId);
    }

    public function processCheckout(SaleService $saleService, $checkoutData, $print = true)
    {
        $cartItems = $checkoutData['cart'] ?? [];
        $discount = (float) ($checkoutData['discount'] ?? 0);
        $paymentMethod = $checkoutData['paymentMethod'] ?? 'cash';
        $paymentReference = $checkoutData['paymentReference'] ?? '';
        $applyRoundOff = $checkoutData['applyRoundOff'] ?? true;
        $notes = $checkoutData['notes'] ?? '';

        if (empty($cartItems)) {
            $this->addError('cart', 'Cart is empty.');

            return;
        }

        $branchId = $this->selectedBranchId;
        if (! $branchId) {
            $this->addError('cart', 'No active branch selected.');

            return;
        }

        try {
            $sale = $saleService->checkout(
                branchId: $branchId,
                userId: auth()->id(),
                customerId: empty($this->customerId) ? null : $this->customerId,
                cartItems: $cartItems,
                discount: $discount,
                paymentMethod: $paymentMethod,
                paymentStatus: 'paid',
                paymentReference: $paymentReference,
                applyRoundOff: $applyRoundOff,
                notes: $notes
            );

            $this->lastSaleId = $sale->id;

            $this->dispatch('checkout-successful');

            if ($print) {
                $this->dispatch('sale-completed', saleId: $sale->id);
            }

            Notification::make()
                ->title(__('messages.sale_completed'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->addError('checkout', $e->getMessage());
        }
    }

    public function printLastInvoice(PricingService $pricingService, $checkoutData = null)
    {
        $cartItems = $checkoutData['cart'] ?? [];

        if (! empty($cartItems)) {
            $discount = (float) ($checkoutData['discount'] ?? 0);
            $applyRoundOff = $checkoutData['applyRoundOff'] ?? true;

            $subTotal = 0.0;
            $taxAmount = 0.0;

            foreach ($cartItems as $item) {
                $isInclusive = $item['is_tax_inclusive'] ?? false;
                $pricing = $pricingService->lineWithTaxRate(
                    (int) ($item['quantity'] ?? 0),
                    (float) $item['unit_price'],
                    (float) ($item['tax_rate'] ?? 0),
                    $isInclusive
                );
                $subTotal += $pricing['line_sub_total'];
                $taxAmount += $pricing['tax_amount'];
            }

            $rawTotal = ($subTotal + $taxAmount) - $discount;
            $roundOffAmount = $applyRoundOff ? round($rawTotal) - $rawTotal : 0;
            $total = $applyRoundOff ? max(0, round($rawTotal)) : max(0, $rawTotal);

            $sale = new \App\Models\Sale([
                'invoice_number' => 'ESTIMATE',
                'created_at' => now(),
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'discount' => $discount,
                'round_off' => $roundOffAmount,
                'total_amount' => $total,
            ]);
            $sale->setRelation('branch', \App\Models\Branch::find($this->selectedBranchId));
            $sale->setRelation('user', auth()->user());
            $sale->setRelation('customer', $this->customerId ? \App\Models\Customer::find($this->customerId) : null);

            $items = collect($cartItems)->map(function ($item) use ($pricingService) {
                $isInclusive = $item['is_tax_inclusive'] ?? false;
                $pricing = $pricingService->lineWithTaxRate(
                    (int) ($item['quantity'] ?? 0),
                    (float) $item['unit_price'],
                    (float) ($item['tax_rate'] ?? 0),
                    $isInclusive
                );
                $saleItem = new \App\Models\SaleItem([
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $pricing['line_total_amount'],
                ]);
                $medicine = new \App\Models\Medicine(['name' => $item['name']]);
                $saleItem->setRelation('medicine', $medicine);

                return $saleItem;
            });
            $sale->setRelation('items', $items);

            $html = view('sales.receipt', [
                'sale' => $sale,
                'isEstimate' => true,
            ])->render();

            $this->dispatch('print-estimate', html: $html);

            return;
        }

        $saleIdToPrint = $this->lastSaleId;

        if (! $saleIdToPrint) {
            $branchId = $this->selectedBranchId;
            $latestSale = \App\Models\Sale::where('branch_id', $branchId)
                ->where('user_id', auth()->id())
                ->latest('id')
                ->first();

            if ($latestSale) {
                $saleIdToPrint = $latestSale->id;
                $this->lastSaleId = $saleIdToPrint;
            }
        }

        if ($saleIdToPrint) {
            $this->dispatch('sale-completed', saleId: $saleIdToPrint);
        } else {
            Notification::make()
                ->title(__('messages.no_recent_sale'))
                ->warning()
                ->send();
        }
    }

    public function holdInvoice($checkoutData)
    {
        $cartItems = $checkoutData['cart'] ?? [];
        if (empty($cartItems)) {
            $this->addError('cart', 'Cart is empty.');

            return;
        }

        $branchId = $this->selectedBranchId;
        if (! $branchId) {
            $this->addError('cart', 'No active branch selected.');

            return;
        }

        // Calculate total for draft
        $discount = (float) ($checkoutData['discount'] ?? 0);

        $pricingService = app(PricingService::class);
        $subTotal = 0;
        $taxAmount = 0;
        foreach ($cartItems as $item) {
            $isInclusive = $item['is_tax_inclusive'] ?? false;
            $pricing = $pricingService->lineWithTaxRate((int) ($item['quantity'] ?? 0), (float) $item['unit_price'], (float) ($item['tax_rate'] ?? 0), $isInclusive);
            $subTotal += $pricing['line_sub_total'];
            $taxAmount += $pricing['tax_amount'];
        }

        $total = ($subTotal + $taxAmount) - $discount;

        $customerName = '';
        if (! empty($this->customerId)) {
            $customer = Customer::find($this->customerId);
            if ($customer) {
                $customerName = $customer->name;
            }
        }

        $referenceName = $checkoutData['referenceName'] ?? ($customerName ?: 'Draft '.date('h:i A'));

        PosDraft::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'customer_id' => empty($this->customerId) ? null : $this->customerId,
            'reference_name' => $referenceName,
            'cart_data' => $checkoutData,
            'total_amount' => $total,
        ]);

        $this->dispatch('draft-saved');

        Notification::make()
            ->title(__('messages.invoice_held'))
            ->success()
            ->send();
    }

    public function loadDraft($draftId)
    {
        $draft = PosDraft::where('branch_id', $this->selectedBranchId)->find($draftId);
        if (! $draft) {
            Notification::make()->title('Draft not found')->danger()->send();

            return;
        }

        $this->customerId = $draft->customer_id;
        $this->selectedCustomerName = $draft->customer ? $draft->customer->name.' ('.$draft->customer->phone.')' : '';

        $this->dispatch('draft-loaded', payload: $draft->cart_data, customerName: $this->selectedCustomerName);

        // Auto delete after loading
        $draft->delete();

        Notification::make()->title(__('messages.draft_loaded'))->success()->send();

        $this->unmountAction();
    }

    public function deleteDraft($draftId)
    {
        $draft = PosDraft::where('branch_id', $this->selectedBranchId)->find($draftId);
        if ($draft) {
            $draft->delete();
            Notification::make()->title(__('messages.draft_deleted'))->success()->send();
        }
    }

    public function viewDraftsAction(): Action
    {
        return Action::make('viewDrafts')
            ->label(__('messages.drafts') ?? 'Drafts')
            ->icon('heroicon-o-document-text')
            ->modalHeading('Saved Drafts')
            ->modalContent(fn () => view('livewire.sales.drafts-modal', [
                'drafts' => PosDraft::where('branch_id', $this->selectedBranchId)
                    ->with('customer')
                    ->latest()
                    ->get(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
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
                $this->selectedCustomerName = $customer->name.' ('.$customer->phone.')';

                $this->dispatch('customer-selected', id: $customer->id, name: $this->selectedCustomerName);

                Notification::make()
                    ->title(__('messages.customer_created') ?? 'Customer created successfully.')
                    ->success()
                    ->send();
            });
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) > 1) {
            $this->customerSearchResults = Customer::where('name', 'like', '%'.$this->customerSearch.'%')
                ->orWhere('phone', 'like', '%'.$this->customerSearch.'%')
                ->take(10)
                ->get();
        } else {
            $this->customerSearchResults = [];
        }
    }

    public function handleCustomerEnter()
    {
        if (count($this->customerSearchResults) === 1) {
            $customer = $this->customerSearchResults->first();
            $this->selectCustomer($customer->id, $customer->name, $customer->phone);
        }
    }

    public function selectCustomer($id, $name, $phone)
    {
        $this->customerId = $id;
        $this->selectedCustomerName = $name.' ('.$phone.')';
        $this->customerSearch = '';
        $this->customerSearchResults = [];
        $this->dispatch('customer-selected', id: $id, name: $this->selectedCustomerName);
    }

    public function clearCustomer()
    {
        $this->customerId = null;
        $this->selectedCustomerName = '';
        $this->dispatch('customer-cleared');
    }

    public function render()
    {
        return view('livewire.sales.pos-terminal');
    }

    public function toJSON()
    {
        return [];
    }
}
